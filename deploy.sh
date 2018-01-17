#!/bin/bash

FTP_URL="ftp://ftp.cluster003.ovh.net/www/subdomains/yttracker/"

gitLastCommit=$(git show --summary --grep="Merge pull request")
if [[ -z "$gitLastCommit" ]]
then
	lastCommit=$(git log --format="%H" -n 1)
else
	printf "We got a Merge Request!"
	arr=(${gitLastCommit})
	lastCommit=${arr[4]}
fi
printf "Last commit: $lastCommit\n\n"

filesChanged=$(git diff-tree --no-commit-id --name-only -r ${lastCommit})
if [ ${#filesChanged[@]} -eq 0 ]; then
    printf "No files to update"
else
	IFS='
	'
    for f in ${filesChanged}
	do
		if [ "$f" != ".travis.yml" ] && [ "$f" != "deploy.sh" ] && [ "$f" != "test.js" ] && [ "$f" != "package.json" ] && [ "$f" != "README.md" ] && [ "$f" != ".gitignore" ]
		then
			if [ ! -f "${f}" ];
			then
   				printf "\nDeleting file ${f}\n"
				STATUSCODE=$(curl -v -o - -s -w "%{http_code}" -u "${FTP_USER}:${FTP_PASS}" "${FTP_URL}" -X "DELE ${f}")
				if test $STATUSCODE -ne 250; then
				    printf "Error deleting file ${f}\n"
				fi
			else
	 			printf "\nUploading file ${f}\n"
	 			curl --ftp-create-dirs -T "${f}" -u "${FTP_USER}:${FTP_PASS}" "${FTP_URL}${f}"
			fi
		fi
    done
fi