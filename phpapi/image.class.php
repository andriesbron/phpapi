<?php
/**
 * @copyright   Copyright (C) 2022, In Our Place. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// @todo clean up, comment... etc. this is a raw dump from a media project.
class image 
{
	private $screens;
	private $thumbsizes;
	
	public function __construct($options = array()) {
		$this->screens = [
				
				"1080p"  => ['x'=>1920, 'y'=>1080, 'r'=>1920/1080]
				, "800p" => ['x'=>1280, 'y'=>800,  'r'=>1280/800]
				, "768p" => ['x'=>1366, 'y'=>768,  'r'=>1366/768]
				, "506p" => ['x'=>900, 'y'=>506,  'r'=>900/506]
				, "360p" => ['x'=>640,  'y'=>360,  'r'=>640/360]
				
				// Vertical screens
				, "800pv" => ['x'=>800,  'y'=>1280,  'r'=>800/1280]
				, "360pv" => ['x'=>360,  'y'=>640,  'r'=>360/640] // pv = vertical
				, "375pv" => ['x'=>375,  'y'=>667,  'r'=>375/667] // pv = vertical
		];
		$this->thumbsizes = [
				"75p"    => ['x'=>133,    'y'=>75,  'r'=>133/75]
				, "120p" => ['x'=>213,    'y'=>120,  'r'=>213/120]
				, "200p" => ['x'=>356,    'y'=>200,  'r'=>356/200]
				, "75s"  => ['x'=>75,    'y'=>75,  'r'=>1]
				, "120s" => ['x'=>120,    'y'=>120,  'r'=>1]
				, "200s" => ['x'=>200,    'y'=>200,  'r'=>1]
		];
	}
	
	public function checkItunesLogo($img)
	{
		$class = $this->imageClassification($img);
		$retVal= [];
		if (
				(intval($class['x']) < 1400)
				|| (intval($class['y']) < 1400)
				|| (intval($class['x']) > 3000)
				|| (intval($class['y']) > 3000)
				) {
					$retVal[] = "Image is too small to be used as feed logo. Must be larger than 1400x1400px and smaller than 3000x3000px.";
					$retVal[] = "This image is ".$class['x']."x".$class['y'];
				}
	}
	
	/**
	 * easy image resize function
	 *       //indicate which file to resize (can be any type jpg/png/gif/etc...)
      $file = 'your_path_to_file/file.png';
      
      //indicate the path and name for the new resized file
      $resizedFile = 'your_path_to_file/resizedFile.png';
      
      //call the function (when passing path to pic)
      smart_resize_image($file , null, SET_YOUR_WIDTH , SET_YOUR_HIGHT , false , $resizedFile , false , false ,100 );
      //call the function (when passing pic as string)
      smart_resize_image(null , file_get_contents($file), SET_YOUR_WIDTH , SET_YOUR_HIGHT , false , $resizedFile , false , false ,100 );
      
	 * @param  $file - file name to resize
	 * @param  $string - The image data, as a string
	 * @param  $width - new image width
	 * @param  $height - new image height
	 * @param  $proportional - keep image proportional, default is no
	 * @param  $output - name of the new file (include path if needed)
	 * @param  $delete_original - if true the original image will be deleted
	 * @param  $use_linux_commands - if set to true will use "rm" to delete the image, if false will use PHP unlink
	 * @param  $quality - enter 1-100 (100 is best quality) default is 100
	 * @return boolean|resource
	 */
	public function smart_resize_image($file,
			$string             = null,
			$width              = 0,
			$height             = 0,
			$proportional       = false,
			$output             = 'file',
			$delete_original    = true,
			$use_linux_commands = false,
			$quality = 100
			) {
				if ( $height <= 0 && $width <= 0 ) return false;
				if ( $file === null && $string === null ) return false;
				

				# Setting defaults and meta
				$info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
				$image                        = '';
				$final_width                  = 0;
				$final_height                 = 0;
				list($width_old, $height_old) = $info;
				$cropHeight = $cropWidth = 0;
				
				# Calculating proportionality
				if ($proportional) {
					if      ($width  == 0)  $factor = $height/$height_old;
					elseif  ($height == 0)  $factor = $width/$width_old;
					else                    $factor = min( $width / $width_old, $height / $height_old );
					
					$final_width  = round( $width_old * $factor );
					$final_height = round( $height_old * $factor );
				}
				else {
					$final_width = ( $width <= 0 ) ? $width_old : $width;
					$final_height = ( $height <= 0 ) ? $height_old : $height;
					$widthX = $width_old / $width;
					$heightX = $height_old / $height;
					
					$x = min($widthX, $heightX);
					$cropWidth = ($width_old - $width * $x) / 2;
					$cropHeight = ($height_old - $height * $x) / 2;
				}
				
				# Loading image to memory according to type
				switch ( $info[2] ) {
					case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;
					case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;
					case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;
					default: return false;
				}
				
				
				# This is the resizing/resampling/transparency-preserving magic
				$image_resized = imagecreatetruecolor( $final_width, $final_height );
				if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
					$transparency = imagecolortransparent($image);
					$palletsize = imagecolorstotal($image);
					
					if ($transparency >= 0 && $transparency < $palletsize) {
						$transparent_color  = imagecolorsforindex($image, $transparency);
						$transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
						imagefill($image_resized, 0, 0, $transparency);
						imagecolortransparent($image_resized, $transparency);
					}
					elseif ($info[2] == IMAGETYPE_PNG) {
						imagealphablending($image_resized, false);
						$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
						imagefill($image_resized, 0, 0, $color);
						imagesavealpha($image_resized, true);
					}
				}
				imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
				
				
				# Taking care of original, if needed
				if ( $delete_original ) {
					if ( $use_linux_commands ) exec('rm '.$file);
					else @unlink($file);
				}
				
				# Preparing a method of providing result
				switch ( strtolower($output) ) {
					case 'browser':
						$mime = image_type_to_mime_type($info[2]);
						header("Content-type: $mime");
						$output = NULL;
						break;
					case 'file':
						$output = $file;
						break;
					case 'return':
						return $image_resized;
						break;
					default:
						break;
				}
				
				# Writing image according to type to the output destination and image quality
				switch ( $info[2] ) {
					case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
					case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
					case IMAGETYPE_PNG:
						$quality = 9 - (int)((0.9*$quality)/10.0);
						imagepng($image_resized, $output, $quality);
						break;
					default: return false;
				}
				
				return true;
	}
	
	function resize_imagejpg($file, $w, $h, $finaldst, $crop) {
		list($width, $height) = getimagesize($file);
		$src = imagecreatefromjpeg($file);
		$ir = $width/$height;
		$fir = $w/$h;
		if($ir >= $fir){
			$newheight = $h;
			$newwidth = $w * ($width / $height);
		}
		else {
			$newheight = $w / ($width/$height);
			$newwidth = $w;
		}
		$xcor = 0 - ($newwidth - $w) / 2;
		$ycor = 0 - ($newheight - $h) / 2;

		$dst = imagecreatetruecolor($w, $h);
		if ($crop) {
			imagecopyresampled($dst, $src, $xcor, $ycor, 0, 0, $newwidth, $newheight,
					$width, $height);
		} else {
			
			//$source = imagecreatefromstring(file_get_contents($src));
			$imageResized = imagescale($src, $w, $h);
			//touch($dst);
			$write = imagepng($imageResized, $dst);
			imagedestroy($source);
			/*
			imagecopyresized ($dst, $src, 0, 0, 0, 0, $newwidth,
					$newheight,$w, $h);
					*/
		}
		imagejpeg($dst, $finaldst);
		imagedestroy($dst);
		return $file;
	}

	function resize_imagegif($file, $w, $h, $finaldst, $crop) {
		list($width, $height) = getimagesize($file);
		$src = imagecreatefromgif($file);
		$ir = $width/$height;
		$fir = $w/$h;
		if($ir >= $fir){
			$newheight = $h;
			$newwidth = $w * ($width / $height);
		}
		else {
			$newheight = $w / ($width/$height);
			$newwidth = $w;
		}
		$xcor = 0 - ($newwidth - $w) / 2;
		$ycor = 0 - ($newheight - $h) / 2;

		$dst = imagecreatetruecolor($w, $h);
		$background = imagecolorallocatealpha($dst, 0, 0, 0, 127);
		imagecolortransparent($dst, $background);
		imagealphablending($dst, false);
		imagesavealpha($dst, true);
		
		if ($crop) {
			imagecopyresampled($dst, $src, $xcor, $ycor, 0, 0, $newwidth, $newheight,
					$width, $height);
		
		} else {
			$source = imagecreatefromstring(file_get_contents($src));
			$imageResized = imagescale($source, $w, $h);
			//touch($dst);
			$write = imagepng($imageResized, $dst);
			imagedestroy($source);
			/*
			imagecopyresized ($dst, $src, 0, 0, 0, 0, $newwidth,
					$newheight,$w, $h);
					*/
		}
		imagegif($dst, $finaldst);
		imagedestroy($dst);
		
		return $file;
	}
	
	function resize_imagepng($file, $w, $h, $finaldst, $crop) {
		list($width, $height) = getimagesize($file);
		$src = imagecreatefrompng($file);
		$ir = $width/$height;
		$fir = $w/$h;
		
		if($ir >= $fir){
			$newheight = $h;
			$newwidth = $w * ($width / $height);
		}
		else {
			$newheight = $w / ($width/$height);
			$newwidth = $w;
		}
		$xcor = 0 - ($newwidth - $w) / 2;
		$ycor = 0 - ($newheight - $h) / 2;
		
		
		$dst = imagecreatetruecolor($w, $h);
		$background = imagecolorallocate($dst, 0, 0, 0);
		imagecolortransparent($dst, $background);
		imagealphablending($dst, false);
		imagesavealpha($dst, true);
		
		if ($crop) {
			imagecopyresampled($dst, $src, $xcor, $ycor, 0, 0, $newwidth,
					$newheight,$width, $height);
		
		} else {
			$source = imagecreatefromstring(file_get_contents($src));
			$imageResized = imagescale($source, $w, $h);
			//touch($dst);
			$write = imagepng($imageResized, $dst);
			imagedestroy($source);
			/*
			imagecopyresized ($dst, $src, 0, 0, 0, 0, $w,
					$h,$width, $height);
					*/
		}
		imagepng($dst, $finaldst);
		imagedestroy($dst);
		
		return $file;
	}
	
	public function ImageResize($file, $w, $h, $finaldst, $crop=True) {
		$getsize = getimagesize($file);
		$image_type = $getsize[2];
		
		if( $image_type == IMAGETYPE_JPEG) {
			
			$this->resize_imagejpg($file, $w, $h, $finaldst, $crop);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			
			$this->resize_imagegif($file, $w, $h, $finaldst, $crop);
		} elseif( $image_type == IMAGETYPE_PNG ) {
			
			$this->resize_imagepng($file, $w, $h, $finaldst, $crop);
		}
	}
	
	/**
	 * return array Image description. If there is no image on the location, it returns name and directory, apparently this is an image to be created.d
	 */
	public function imageClassification ($img)
	{
		$info               = array();
		$class              = array();
		
		if (file_exists($img)) {
			$size = getimagesize($img,$info);
			$class = [
					'x'      =>$size[0]
					, 'y'    =>$size[1]
					, 'mime' => $size['mime']
					, 'r'    => $size[0]/$size[1]
			];
		}
		/*
		$nameArr            = explode(DS,$img);
		$class['name']      = $nameArr[count($nameArr)-1];
		unset($nameArr[count($nameArr)-1]);
		$class['directory'] = implode(DS,$nameArr);
		$class['directory'] = $class['directory'].DS;
		*/
		$class = array_merge($class,$this->getFileDetails($img));

		return $class;
	}
	
	function safe_dirname($path)
	{
		$dirname = dirname($path);
		
		return $dirname == '/' ? '' : $dirname;
	}
	
	public function getFileDetails($file) {
		$details = [];

		$details['name']=basename($file);
		$nameArr              = explode($details['name'],$file);
		$details['directory'] = $nameArr[0];

		return $details;
	}
	
	/**
	 * @param array $screens array of screen definitions by keys of type. A single definition must be according to, e.g.: ['x'=>1366,    'y'=>768,  'r'=>1366/768] which can be given key value "768p"
	 */
	public function loadNewSetOfScreens($screens)
	{
		$this->screens = $screens;
		[
				"768p"    => ['x'=>1366,    'y'=>768,  'r'=>1366/768]
				, "1080p" => ['x'=>1920, 'y'=>1080, 'r'=>1920/1080]
				, "900p"  => ['x'=>1440,  'y'=>900,  'r'=>1440/900]
				, "800p"  => ['x'=>1280,  'y'=>800,  'r'=>1280/800]
				, "360pp" => ['x'=>360,  'y'=>640,  'r'=>360/640]
				, "375pp" => ['x'=>375,  'y'=>667,  'r'=>375/667]
		];
	}
	
	/**
	 * @desc Rescales an image for a screen so that it fits on that screen.
	 */
	public function cropForScreen($imgclassin, $screen, $dirout=False)
	{
		//echo "<h3>Building for: ".$screen."</h3>";
		$xresize = $imgclassin['y']/$this->screens[$screen]['y'];
		if ($this->screens[$screen]['r'] >= 1) {
			if ($dirout !== False) {
				//echo "creating at: ".$dirout.$screen.".jpg";
				$this->ImageResize($imgclassin['directory'].$imgclassin['name'], $this->screens[$screen]['x'], $this->screens[$screen]['y'], $dirout.$screen.".jpg", $crop=True);
				
			} else {
				$this->ImageResize($imgclassin['directory'].$imgclassin['name'], $this->screens[$screen]['x'], $this->screens[$screen]['y'], $screen.".jpg", $crop=True);
			}
		}
		
	}
	
	public function setThumbSizes ($newsizes)
	{
		$thumbsizes=[];
		if (is_array($newsizes)) {
			foreach ($newsizes as $resolution => $screen) {
				if (array_key_exists('x', $screen))
					if (array_key_exists('y', $screen))
						$thumbsizes[$resolution]=$screen;
			}
		}
		if (count($thumbsizes)>0)
			$this->thumbsizes = $thumbsizes;
	}
	
	public function createThumbSet ($img, $outdir, $quality=80)
	{
		$class = $this->imageClassification ($img);
		//echo "<p>";
		//echo "Creating thumbs from image: ". $img."<br>";
		//echo "Output directory: ". $outdir."<br>";
	
		foreach ($this->thumbsizes as $res => $timg) {
			//echo "Set".$res." X:".$timg['x']. " Y:".$timg['y']."<br>";
			$this->smart_resize_image ($class['directory'].$class['name'], null, $timg['x'], $timg['y'], false , $outdir.$res.".jpg", false , false ,$quality);
			
		}
		
		// And create some favicons as well...
		$this->smart_resize_image($class['directory'].$class['name'], null, 64, 64, false , $outdir."favicon_64.gif", false , false ,80 );
		$this->smart_resize_image($class['directory'].$class['name'], null, 32, 32, false , $outdir."favicon_32.gif", false , false ,80 );
		$this->smart_resize_image($class['directory'].$class['name'], null, 16, 16, false , $outdir."favicon_16.gif", false , false ,80 );
	}
	
	public function createImageSet ($img, $outdir, $quality=80)
	{
		$class = $this->imageClassification ($img);
		foreach ($this->screens as $res => $timg) {
			//echo "Set".$res." X:".$timg['x']. " Y:".$timg['y']."<br>";
			$this->smart_resize_image ($class['directory'].$class['name'], null, $timg['x'], $timg['y'], false , $outdir.$res.".jpg", false , false ,$quality);
		}
	}
	
	/**
	 * @deprecated use createThumbSet instead
	 * @param unknown $img
	 * @param unknown $outimg
	 */
	public function createThumbsForImg ($img, $outimg)
	{
		$class = $this->imageClassification ($img);
		if (!$outimg) {
			$outimg=$class['directory'];
		}

		
		//Create only HD ratio thumbs:
		$this->smart_resize_image($class['directory'].$class['name'], null, 320, 180, false , $outimg."320_1080.jpg", false , false ,80 );
		$this->smart_resize_image($class['directory'].$class['name'], null, 192, 108, false , $outimg."192_1080.jpg", false , false ,80 );
		$this->smart_resize_image($class['directory'].$class['name'], null, 120, 68, false , $outimg."120_1080.jpg", false , false ,80 );
		// A few squares, nice if needed:
		$this->smart_resize_image($class['directory'].$class['name'], null, 80, 80, false , $outimg."80_sq.jpg", false , false ,80 );
		$this->smart_resize_image($class['directory'].$class['name'], null, 120, 120, false , $outimg."120_sq.jpg", false , false ,80 );
		// And some favicons, why not?
		$this->smart_resize_image($class['directory'].$class['name'], null, 64, 64, false , $outimg."favicon_64.gif", false , false ,80 );
		$this->smart_resize_image($class['directory'].$class['name'], null, 32, 32, false , $outimg."favicon_32.gif", false , false ,80 );
		$this->smart_resize_image($class['directory'].$class['name'], null, 16, 16, false , $outimg."favicon_16.gif", false , false ,80 );
		
	}
	
	/**
	 * @desc Rescales an image for a screen so that it fits on that screen.
	 * 
	 */
	public function scaleForScreen ($imgclassin, $screen, $dirout=False)
	{
		if ($dirout !== False) {
			//echo "<br>From: ".$imgclassin['directory'].$imgclassin['name']."<br>";
			//echo $dirout.$screen.".jpg<br>";
			
			$this->smart_resize_image($imgclassin['directory'].$imgclassin['name'], null, $this->screens[$screen]['x'], $this->screens[$screen]['y'], true , $dirout.$screen.".jpg", false , false ,80 );
		} else {
			$this->smart_resize_image($imgclassin['directory'].$imgclassin['name'], null, $this->screens[$screen]['x'], $this->screens[$screen]['y'], true , $imgclassin['directory'].$screen.".jpg", false , false ,80 ); 
		}
	}
	
	public function generateThumbs ($img, $dirout=False)
	{
		//echo "<h1>Making thumbs</h1>";
		$class = $this->imageClassification ($img);
		//print_r($class);
		
		foreach ($this->thumbsizes as $format => $desc) {
			//print_r($format);
			//$this->cropForScreen($class, $format, $dirout=$dirout);
			$this->scaleForScreen($class, $format, $dirout=$dirout);
			//break;
		}
		
	}
	
	
	public function createOriginal ($img, $dirout=False)
	{
		$class = $this->imageClassification ($img);
		$this->smart_resize_image($class['directory'].$class['name'], null, $class['x'], $class['y'], true , $dirout."media.jpg", false , false ,80 );
		//$this->ImageResize($class['directory'].$class['name'], $class['x'], $class['y'], $dirout."media.jpg", $crop=True);
		
	}
	
	public function generateForScreens ($img, $dirout=False)
	{
		$class = $this->imageClassification ($img);
		// Default screen suitables
		$suitablefor = [
				"360pv" => $this->screens['360pv']
				, "375pv" => $this->screens['375pv']
		];

		// Check if greater resolutions make sense:
		foreach ($this->screens as $format => $desc) {
			// Good for cropping:			
			if (($class['x'] == $class['x']) && $format == "800pv") {
				// nothing
				// if images are square, I don't need the pv format version, p is enough in that case.
			} else {
				if ($class['x'] >= $desc['x'])
					if ($class['y'] >= $desc['y'])
						$suitablefor[$format]=$desc;
			}
		}
	
		foreach ($suitablefor as $format => $desc) {
			/*
			echo "<hr>";
			echo $format;
			echo "<br>".$dirout."<br>";
			print_r($class);
			echo "<hr>";
			*/
			$this->cropForScreen($class, $format, $dirout=$dirout);
			$this->scaleForScreen ($class, $format, $dirout=$dirout);
			//break;
		}

		
		//$this->createThumbsForImg($img);
		
		/*
		// Delete the copy image if created.
		if ($deleteFile !== False) {
			unlink($deleteFile);
		}
		
		*/
	}
	

}
