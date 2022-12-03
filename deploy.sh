#!/bin/bash
echo ""
echo ""
echo "=================================="
echo "### Retenvo Automation CLI #######"
echo "### Created by Victory Osayi #####"
echo "### victoryosayi@retenvo.com #####"
echo "### Deploy dev codes to server ###"
echo "=================================="
echo "- NOTICE: This script is customizable and should not be run from production server. It's only a shortcut to frequently used git add/commit and push to remote operation."
echo "- This script will commit/merge current \"dev\" branch with master branch and push the codes to a remote \"production\" branch."
echo "- A \"git remote production <server-remote-url-or-remote-bare-repo-url>\" must be setup before proceeding."
echo "==============================="

read -p "Proceed? Type [Y or N]: " var_proceed

if [ "$var_proceed" = "Y" ] || [ "$var_proceed" = "y" ] || [ "$var_proceed" = "YES" ] || [ "$var_proceed" = "Yes" ] || [ "$var_proceed" = "yes" ]; then
    echo " "
    echo "Pushing codes to server..."
    echo " "
    read -p "Enter Git Commit Message? (Deployed: ):" var_commit_msg
    echo " "
    git add .
    if [ "$var_commit_msg" = "" ]; then
        git commit -m "Deployed..."
    else
        git commit -m "Deployed: $var_commit_msg"
    fi
    git checkout master && git pull origin master
    git checkout dev && git rebase master && git checkout master
    git merge dev && git checkout dev
    git push production master
    git push origin master
else
    echo " "
    echo "Operation cancelled."
    echo " "
fi