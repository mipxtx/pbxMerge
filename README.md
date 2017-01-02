#pbxMerge

pbxMerge is a merge tool of Xcode project file 

##The problem
While you developing new features in several branches, you can not auto merge project.pbxproj file, to create the release branch, wich contains all af your new features. 

If you add some resources(like code files) in more then one feature branch, Xcode add it at the same place and you get content confilct while merging this branch into new release candidate.
 
##The Solution
pbxMerge creates separate diffs of project.pbxproj file, which can be automatically merged by git.

Merge process operates in two stages
1. It creates a diff between local project.pbxproj and other diffs, which processed before. The result of merge of all diffs(current and other) should be equal of current project.pbxproj. This stage starts before commit.
2. Assembly of diffs into project.pbxproj to build Xcode project. This stage starts after merge.
 
##How to use
###tl;dr
`cp pbxMerge/build/* you/project/;`

`cd you/project;`

`mkdir -p .git/hooks;`

`echo './export path/to/project.pbxproj' > .git/hooks/pre-commit;`

`echo './import path/to/project.pbxproj'>.git/hooks/post-merge; chmod 775 .git/hooks/post-merge;`

`chmod 775 pbx.phar export import .git/hooks/pre-commit .git/hooks/post-merge;`

###description

The best way to use utility is to use it with git hooks.
At the build folder you can find pbx.phar utility and import/export bash scripts, that can help use utility with hooks. This files shoud be stored at the root of your project (because hooks works on it). import/export scripts requires 1 parameter - path to the project.pbxproj file.

1. pre-commit hook starts a diff processing and stop commit process on changes at diff. In this case you can see the changes at diffs and commit you code again with a new diff. Put the pre-commit file at the .git/hooks folder of your project with call of export script: `echo './export path/to/project.pbxproj' > .git/hooks/pre-commit; chmod 775 .git/hooks/pre-commit ` 
2. post-merge hook start project file assembly with the diff files. `echo './import path/to/project.pbxproj'>.git/hooks/post-merge; chmod 775 .git/hooks/post-merge`    

