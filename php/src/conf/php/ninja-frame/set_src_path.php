<?
/**
 * Add path to shared src dir.
 * @param string $cvsproj The name of the directory within /web/app-src/ where your source code resides.  Your cvs project name, ideally.
 * @param mixed $target Optional target within shared directory to add to include path.  Example: passing 'public' as 2nd param would add 'src/public' to the path.
 *
 * @package NinjaPHPFramework
 */
/**
 * Set PHP's include path for this project
 * @param mixed $cvsproj CVS/Subversion Project Name
 * @param mixed $target project source subdirectory. Example: public would point to /web/app-src/my-svn-name/src/public
 * @return bool true
 */
function setSrcPath($cvsproj=false, $target=false){
	
	# These paths should make sure the application runs in non-deployed (CVS) structure.
	$src_dirs[] = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/src' ;
	$src_dirs[] = dirname(dirname($_SERVER['PATH_TRANSLATED'])) . '/src' ;
	$src_dirs[] = dirname(getcwd()) . '/src' ;
	
	if ($target){
		$src_dirs[] = dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . "/src/$target" ;
		$src_dirs[] = dirname(dirname($_SERVER['PATH_TRANSLATED'])) . "/src/$target" ;
		$src_dirs[] = dirname(getcwd()) . "/src/$target" ;
	}

	if ($cvsproj){
		# This should be the deployed location of src dir.  Ideally, this would be the name of the cvs project.
		$src_dirs[] = "/web/app-src/$cvsproj" ;
		$src_dirs[] = "/web/app-src/$cvsproj/src" ;
		//$src_dirs[] = "/web/app-bin/www.ark.org/newtvr/src";
		//$src_dirs[] = "/web/app-bin/www.ark.org/newtvr/src/$target";

		if ($target){
			$src_dirs[] = "/web/app-src/$cvsproj/$target" ;
			$src_dirs[] = "/web/app-src/$cvsproj/src/$target" ;
		}
	}
	
	$src_dirs = array_unique($src_dirs) ;
	
	# Convert to string and save to include_path.
	$src_dirs_str = join(PATH_SEPARATOR, $src_dirs) ;
	ini_set('include_path', join(PATH_SEPARATOR, array($src_dirs_str,ini_get('include_path')))) ;
	return true ;
}