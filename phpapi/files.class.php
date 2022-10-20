
/**
 * Use with care, no refunds.
 */
class files
{
    
	/**
	 * @param unknown $structure
	 * @param system $path
	 */
	public function make_dir_tree($structure, $path=__DIR__)
	{
		foreach ($structure as $folder => $sub_folder) {
			if (is_array($sub_folder)) {
				// Folder with subfolders
				$new_path = "{$path}/{$folder}";
				if ( !is_dir($new_path)) {
					mkdir($new_path, $mode=0777);
				}
				self::make_dir_tree($sub_folder, $new_path);
			} else {
				$new_path = "{$path}/{$sub_folder}";
				if ( !is_dir($new_path)) {
					mkdir($new_path, $mode=0777);
				}
			}
		}
	}
	
	/**
	 * @desc unlike glob does scandir also find hidden files and directories.
	 * @param unknown $dir
	 * @return boolean
	 */
	public function delTree($dir) {
		$files = array_diff(scandir($dir), array('.','..'));
		foreach ($files as $file) {
			(is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
		}
		rmdir($dir);
		
		return True;
	} 
	
	public function is_dir_empty ($dir) {
		if (!is_readable($dir)) return NULL;
		return (count(scandir($dir)) == 2);
	}
	
	/**
	 * @deprecated Use $this->delTree($dir) instead.
	 * @param unknown $path
	 * @return boolean
	 */
	public function removeDirectory($path) {
		$files = glob($path . "/*");
		foreach ($files as $file) {
			is_dir($file) ? $this->removeDirectory($file) : unlink($file);
		}
		rmdir($path);
		
		return True;
	}
	
	/**
	 * @param unknown $directory
	 * @return number Directory size in kB.
	 */
	function dirSize($directory) {
		if ($directory == "") {
			$directory = ".";
		}
		$size = 0;
		
		if (file_exists($directory)) {
			foreach(new RecursiveIteratorIterator (new RecursiveDirectoryIterator($directory)) as $file){
				$size += $file -> getSize();
			}
			//@todo if divided by 995 it comes more close to what osx says it should be.
			return round($size);
		} else {
			return 0;
		}
	}
	
	public function foldersize ($path) {
		$total_size = 0;
		$files = scandir($path);
		
		foreach($files as $t) {
			if (is_dir(rtrim($path, '/') . '/' . $t)) {
				if ($t<>"." && $t<>"..") {
					$size = $this->foldersize(rtrim($path, '/') . '/' . $t);
					$total_size += $size;
				}
			} else {
				$size = filesize(rtrim($path, '/') . '/' . $t);
				$total_size += $size;
			}
		}
		return $total_size;
	}
	
	public function format_size($size) {
		$mod = 1024;
		$units = explode(' ','B KB MB GB TB PB');
		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
		}
		
		return round($size, 2) . ' ' . $units[$i];
	}
}

class debug
{
    private $level=0; // 0=None
    
    public function setLevel($level)
    {
        $this->level = $level;
    }
    
    public function p($data, $level, $die=False)
    {
        if ($this->level > 0) {
            if ($level <= $this->level) { 
                echo "<pre>";
                print_r($data);
                echo "</pre>";
                if ($die) die;
            }
        }
    }   
}
