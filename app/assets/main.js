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
