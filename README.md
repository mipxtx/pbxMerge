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

`chmod 775 pbx.phar;`

`./pbx.par setup --path=path\to\project.pbxproj`


###description

The best way to use utility is to use it with git hooks.
You can use setup option of utility, that can generate you all neded hooks 
