#!/bin/bash
gitLastCommit=$(git show --summary --grep="Merge pull request")
if [[ -z "$gitLastCommit" ]]
then
	lastCommit=$(git log --format="%H" -n 1)
else
	printf "We got a Merge Request!"
	arr=($gitLastCommit)
	lastCommit=${arr[4]}
fi
printf "Last commit: $lastCommit"

filesChanged=$(git diff-tree --no-commit-id --name-only -r $lastCommit)
if [ ${#filesChanged[@]} -eq 0 ]; then
    printf "No files to update"
else
    for f in $filesChanged
	do
		if [ "$f" != ".travis.yml" ] && [ "$f" != "deploy.sh" ] && [ "$f" != "test.js" ] && [ "$f" != "package.json" ] && [ "$f" != "README.md" ] && [ "$f" != ".gitignore" ]
		then
	 		printf "\nUploading file $f"
	 		curl --ftp-create-dirs -T $f -u $FTP_USER:$FTP_PASS ftp://ftp.cluster003.ovh.net/www/subdomains/yttracker/$f
		fi
	done
fi