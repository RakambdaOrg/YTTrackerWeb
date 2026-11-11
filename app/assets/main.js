const API = '/api/v2';
const byId = id => document.getElementById(id);

/** Same as the extension's formatDuration(): "1D 2H 3M 4S". */
export function formatDuration(ms) {
	const total = Math.max(0, Math.round(ms));
	const text = [
		[Math.floor(total / 86_400_000), 'D'],
		[Math.floor(total / 3_600_000) % 24, 'H'],
		[Math.floor(total / 60_000) % 60, 'M'],
		[Math.floor(total / 1000) % 60, 'S'],
	].filter(([value]) => value > 0).map(([value, unit]) => `${value}${unit}`);
	return text.length > 0 ? text.join(' ') : '0S';
}

async function getJson(path) {
	const response = await fetch(API + path);
	if (!response.ok) {
		throw new Error(`${path}: HTTP ${response.status}`);
	}
	return response.json();
}

const getUsers = async range => (await getJson(`/users?range=${range}`)).users;

// Users table: [name, total opened, watched, count, week ×3, day ×3], durations in ms.
const COUNT_COLUMNS = new Set([3, 6, 9]);
const tableBody = byId('usersBody');
let rows = [];
let sort = {col: 8, desc: true};

function message(text) {
	const td = document.createElement('td');
	td.className = 'message';
	td.colSpan = 10;
	td.textContent = text;
	const tr = document.createElement('tr');
	tr.append(td);
	tableBody.replaceChildren(tr);
}

function renderTable() {
	if (rows.length === 0) {
		message('No active user');
		return;
	}
	const {col, desc} = sort;
	const sorted = rows.toSorted((a, b) => (col === 0 ? a[0].localeCompare(b[0]) : a[col] - b[col]) * (desc ? -1 : 1));
	tableBody.replaceChildren(...sorted.map(row => {
		const tr = document.createElement('tr');
		tr.append(...row.map((value, i) => {
			const td = document.createElement('td');
			if (i === 0) {
				td.className = 'name';
				td.textContent = value;
			} else {
				td.textContent = COUNT_COLUMNS.has(i) ? String(value) : formatDuration(value);
			}
			return td;
		}));
		return tr;
	}));
	for (const button of byId('usersHead').querySelectorAll('button')) {
		const th = button.parentElement;
		if (Number(button.dataset.col) === col) {
			th.setAttribute('aria-sort', desc ? 'descending' : 'ascending');
		} else {
			th.removeAttribute('aria-sort');
		}
	}
}

byId('usersHead').addEventListener('click', event => {
	const button = event.target.closest('button');
	if (!button) {
		return;
	}
	const col = Number(button.dataset.col);
	// Numbers start with the highest first, names in alphabetical order.
	sort = {col, desc: col === sort.col ? !sort.desc : col !== 0};
	renderTable();
});

async function loadTable() {
	try {
		const users = await getUsers(14);
		const stats = await Promise.all(users.map(user => getJson(`/${encodeURIComponent(user.uuid)}/stats`).catch(error => {
			console.error(error);
			return null;
		})));
		rows = users.flatMap((user, i) => {
			const s = stats[i];
			if (!(s?.total?.count > 0)) {
				return [];
			}
			return [[user.username, ...['total', 'week', 'day'].flatMap(period => [s[period].opened * 1000, s[period].watched * 1000, s[period].count])]];
		});
		renderTable();
	} catch (error) {
		console.error(error);
		message('Could not load the stats.');
	}
}

loadTable();

// Charts: one line per user, one chart per measure (never two scales on one chart).
const {Chart} = window;
const HOUR = 3_600_000;
const USER_COLORS = 8;
// Past 8 users the colours repeat with a dash pattern, so no two users look the same.
const DASHES = [[], [6, 4], [2, 3]];
const CHARTS = [
	{id: 'chartWatched', path: 'stats/watched', duration: true},
	{id: 'chartOpened', path: 'stats/opened', duration: true},
	{id: 'chartCount', path: 'stats/opened-count', duration: false},
];

let token;
const charts = new Map();
let allUuids = [];
let loadId = 0;

// Vertical line on the hovered day, as in the extension.
const hoverLine = {
	id: 'hoverLine',
	afterDatasetsDraw: ({ctx, chartArea, tooltip}) => {
		const x = tooltip?.getActiveElements()[0]?.element.x;
		if (x === undefined) {
			return;
		}
		ctx.save();
		ctx.strokeStyle = token('--muted');
		ctx.beginPath();
		ctx.moveTo(x, chartArea.top);
		ctx.lineTo(x, chartArea.bottom);
		ctx.stroke();
		ctx.restore();
	},
};

/** A user's style follows the user, not their rank: it comes from their place in the full user list. */
function userStyle(uuid) {
	const index = Math.max(0, allUuids.indexOf(uuid));
	return {
		color: token(`--user-${(index % USER_COLORS) + 1}`),
		dash: DASHES[Math.floor(index / USER_COLORS) % DASHES.length],
	};
}

function drawChart({id, duration}, users, series) {
	const labels = [...new Set(series.flat().map(point => point.date))].sort();
	const pointRadius = labels.length > 60 ? 0 : 3;
	const datasets = users.map((user, i) => {
		const byDate = new Map(series[i].map(point => [point.date, Number(point.value)]));
		const {color, dash} = userStyle(user.uuid);
		return {
			label: user.username,
			data: labels.map(date => {
				const value = byDate.get(date);
				return value === undefined ? null : duration ? value / HOUR : value;
			}),
			borderColor: color,
			backgroundColor: color,
			borderDash: dash,
			borderWidth: 2,
			pointRadius,
			pointHoverRadius: 5,
			spanGaps: true,
		};
	});
	charts.get(id)?.destroy();
	charts.set(id, new Chart(byId(id), {
		type: 'line',
		plugins: [hoverLine],
		data: {labels, datasets},
		options: {
			maintainAspectRatio: false,
			animation: false,
			interaction: {mode: 'index', intersect: false},
			color: token('--text'),
			scales: {
				x: {ticks: {color: token('--muted')}, grid: {color: token('--border')}},
				y: {
					min: 0,
					title: {display: true, text: duration ? 'Hours' : 'Videos', color: token('--muted')},
					ticks: {precision: duration ? undefined : 0, color: token('--muted')},
					grid: {color: token('--border')},
				},
			},
			plugins: {
				legend: {position: 'bottom', labels: {color: token('--text')}},
				tooltip: {
					itemSort: (a, b) => b.parsed.y - a.parsed.y,
					filter: item => item.parsed.y !== null,
					callbacks: {
						label: item => `${item.dataset.label}: ${duration ? formatDuration(item.parsed.y * HOUR) : item.parsed.y}`,
					},
				},
			},
		},
	}));
}

async function loadCharts() {
	const id = ++loadId;
	const range = Number(byId('period').value);
	const status = byId('chartsStatus');
	status.hidden = true;
	try {
		const css = getComputedStyle(document.documentElement);
		token = name => css.getPropertyValue(name).trim();
		if (allUuids.length === 0) {
			allUuids = (await getJson('/users')).users.map(user => user.uuid);
		}
		const users = await getUsers(range);
		const results = await Promise.all(CHARTS.map(chart => Promise.all(users.map(user =>
			getJson(`/${encodeURIComponent(user.uuid)}/${chart.path}?range=${range}`)))));
		if (id !== loadId) {
			return; // A newer period was picked meanwhile.
		}
		CHARTS.forEach((chart, i) => drawChart(chart, users, results[i]));
	} catch (error) {
		console.error(error);
		status.textContent = 'Could not load the charts.';
		status.hidden = false;
	}
}

/** Shows or hides every user in the three charts; the legend still toggles one user in one chart. */
function setAllUsersVisible(visible) {
	for (const chart of charts.values()) {
		chart.data.datasets.forEach((_, i) => chart.setDatasetVisibility(i, visible));
		chart.update();
	}
}

byId('showAllUsers').addEventListener('click', () => setAllUsersVisible(true));
byId('hideAllUsers').addEventListener('click', () => setAllUsersVisible(false));
byId('period').addEventListener('change', loadCharts);
// The charts read their colours from the CSS tokens: redraw when the theme changes.
matchMedia('(prefers-color-scheme: dark)').addEventListener('change', loadCharts);
loadCharts();
