@echo off
git add .
git status
git commit -m "fix:"
git checkout main
git merge cycloid
git push origin main
git checkout cycloid