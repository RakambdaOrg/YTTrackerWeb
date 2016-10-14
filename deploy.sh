#!/bin/bash
gitLastCommit=$(git show --summary --grep="Merge pull request")
if [[ -z "$gitLastCommit" ]]
then
	lastCommit=$(git log --format="%H" -n 1)
else
	echo "We got a Merge Request!"
	arr=($gitLastCommit)
	lastCommit=${arr[4]}
fi
echo $lastCommit

filesChanged=$(git diff-tree --no-commit-id --name-only -r $lastCommit)
if [ ${#filesChanged[@]} -eq 0 ]; then
    echo "No files to update"
else
    for f in $filesChanged
	do
		if [ "$f" != ".travis.yml" ] && [ "$f" != "deploy.sh" ] && [ "$f" != "test.js" ] && [ "$f" != "package.json" ] && [ "$f" != "README.md" ] && [ "$f" != ".gitignore" ]
		then
	 		echo "\nUploading $f"
	 		curl --ftp-create-dirs -T $f -u $FTP_USER:$FTP_PASS ftp://ftp.cluster003.ovh.net/www/subdomains/yttracker/$f
		fi
	done
fi