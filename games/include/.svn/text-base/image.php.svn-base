<?php

define('UPLOAD_MAX_FILESIZE', intval(str_replace('M', '000000',ini_get('upload_max_filesize'))));

class Image {

	private function _ext($fileName) {
		$fna = pathinfo($fileName, PATHINFO_EXTENSION);
		return strtolower($fna);
	}
	
	public function Save($file_name, $img, $quality=95) {
		switch ($this->_ext($file_name)) {
			case 'bmp': imagewbmp($img, $file_name);
			         break;
			case 'jpg': imagejpeg($img, $file_name, $quality);
			         break;
			case 'gif': imagegif($img, $file_name);
			         break;
			case 'png': imagepng($img, $file_name);
			         break;
			default : return false;         
		}
		chmod($file_name, 0644);
		return true;
	}

	public function CreateImageFromFile($file_name) {
		$img = null;
		switch ($this->_ext($file_name)) {
			case 'gd': $img = imagecreatefromgd($file_name);
			         break;
			case 'bmp': $img = imagecreatefrombmp($file_name);
			         break;
			case 'jpg': $img = imagecreatefromjpeg($file_name);
			         break;
			case 'gif': $img = imagecreatefromgif($file_name);
			         break;
			case 'png': $img = imagecreatefrompng($file_name);
			         break;
			default: 
                    $img = imagecreatefromjpeg($file_name);
                    break;
		}
		return $img;
	}
	
	public function Convert($source, $distance, $quality=80) {
		$res = false;
		if ($source != $distance) {
			if (Image::_ext($source) != Image::_ext($distance)) {
				if ($img = $this->CreateImageFromFile($source)) {
					$res = $this->Save($distance, $img, $quality) != null;
				}
			} else {
				$res = copy($source, $distance);
				chmod($distance, 0644);
			}
		} else $res = true;
		return $res;
	}
	
	public function newExt($fileName, $newext) {
		$newext = strtolower($newext);
		if (Image::_ext($fileName) != $newext) {
			$fna = explode('.', $fileName);
			array_pop($fna);
			return implode('.', $fna).'.'.$newext;
		} else return $fileName;
	}

	public function Resize($file_name, $max_width_resize=0, $max_height_resize=0, $dest_file='', $quality=80) {
		if (!$dest_file) $dest_file = $file_name;
		$dest_file = strtolower($dest_file);
		$file_name = strtolower($file_name);
		if (($max_width_resize > 0) || ($max_height_resize > 0)) {
			if ($img = $this->CreateImageFromFile($file_name)) {
				$img_width   = imagesx($img);
				$img_height  = imagesy($img);
				
				if (($max_width_resize > 0) && ($img_width > $max_width_resize)) {
					$img_height  = $img_height * $max_width_resize / $img_width;
					$img_width   = $max_width_resize;
				}
				
				if (($max_height_resize > 0) && ($img_height > $max_height_resize)) {
					$img_width  = $img_width * $max_height_resize / $img_height;
					$img_height   = $max_height_resize;
				}
				
				if ((imagesx($img) != $img_width) || (imagesy($img) != $img_height)) {
					$img_desc   = imagecreatetruecolor($img_width, $img_height);
					if (imagecopyresampled($img_desc, $img, 0, 0, 0, 0,
					                   $img_width, $img_height,
					                   imagesx($img), imagesy($img))) {
						imagedestroy($img);
						$this->Save($dest_file, $img_desc, $quality);
						imagedestroy($img_desc);
					} else return 'Ошибка при изменении размера';
				} else if ($dest_file != $file_name) {
					if (Image::Convert($file_name, $dest_file, $quality)) {
						return getimagesize($dest_file);
					} else return 'Ошибка при конвертации/копировании файла';
				}	
				return array('width'=>$img_width, 'height'=>$img_height);
			} else return 'Формат не поддерживается';
		} else return 'Не заданы параметры изменения размера';
	}
	
	public function drawCenterText($imageFilePath, $text='', $fontFilePath='arial.ttf', 
						$size=12, $color=array(0, 0, 0, 0), $angle=0, $alpha=100, $quality=80) {
		if ($text && ($img = $this->CreateImageFromFile($imageFilePath))) {
			$img_width  = imagesx($img);
			$img_height = imagesy($img);
			$resize		= true;
			while ($resize) {
				$box	= imagettfbbox ($size, 0, $fontFilePath, $text);
				$width 	= abs($box[4] - $box[0]);
				$height = abs($box[5] - $box[1]);
				if ($resize = $img_width < $width) {
					$size--;
				}
			}
			$icolor		= imagecolorallocatealpha($img, $color[0], $color[1], $color[2], $color[3]);
			$scolor		= imagecolorallocatealpha($img, 0, 0, 0, $color[3]);

			$x = ($img_width - $width) /2;
			$y = ($img_height + $height) /2;

			imagettftext ($img, $size, 0, $x + 2, $y + 2, $scolor, $fontFilePath, $text);
			imagettftext ($img, $size, 0, $x, $y, $icolor, $fontFilePath, $text);
			$this->Save($imageFilePath, $img, $quality);
		} 
	}
	
	public function Upload($POSTFILE, $newfilename='file', $max_filesize=UPLOAD_MAX_FILESIZE) {
		if ($POSTFILE && (trim($POSTFILE['name']) > '')) {
			$userfile_name    = $POSTFILE['name'];
			$userfile         = $POSTFILE['tmp_name'];
			$userfile_size    = $POSTFILE['size'];
			$userfile_type    = $POSTFILE['type'];
			$userfile_error   = $POSTFILE['error'];
			
			$ext = $this->_ext($userfile_name);
			
			if ($userfile_size > $max_filesize) {
				return "максимальный размер файла должен быть ".$max_filesize;
			}
			
			if ($userfile_error > 0) {
				switch ($userfile_error) {
					case 1 : return 'размер файла больше разрешеного UPLOAD_MAX_FILESIZE = '.UPLOAD_MAX_FILESIZE; break;
					case 2 : return 'размер файла больше '.$max_filesize; break;
					case 3 : return 'загружена только часть файла'; break;
					case 4 : return 'файл не загружен'; break;
				}
				return 'неизвестная ошибка, error: '.$userfile_error;
			}
			if (!$this->_ext($newfilename)) $newfilename .= '.'.$ext;
			$result = array('fileName'=>$newfilename, 'Ext'=>$ext);
			if (move_uploaded_file($userfile, $result['fileName'])) {
				chmod($result['fileName'], 0644);
				return $result;
			} else return 'Ошибка при копировании файла '.$userfile.' в '.$result['fileName'].' (v_images File.Upload)';
		} else return 'не задано имя файла (v_images File.Upload)';
	}
	
    function flip ( $image, $mode ) {
 /*   
        $width                        =    imagesx ( $imgsrc );
        $height                       =    imagesy ( $imgsrc );
    
        $src_x                        =    0;
        $src_y                        =    0;
        $src_width                    =    $width;
        $src_height                   =    $height;
    
        switch ( $mode ) {
            case 1: //vertical
                $src_y                =    $height;
                $src_height           =    -$height;
            break;
            case 2: //horizontal
                $src_x                =    $width;
                $src_width            =    -$width;
            break;
            case 3: //both
                $src_x                =    $width;
                $src_y                =    $height;
                $src_width            =    -$width;
                $src_height           =    -$height;
            break;
            default:
                return $imgsrc;
    
        }
        $imgdest = imagecreatetruecolor ( $width, $height );
        if ( imagecopyresampled ( $imgdest, $imgsrc, 0, 0, $src_x, $src_y , $width, $height, $src_width, $src_height ) ) {
//            echo 'FLIP '.$src_x.' '.$src_y.' '.$src_width.' '.$src_height;
            return $imgdest;
        }
    
        return $imgsrc;
        */
        $w = imagesx($image);
        $h = imagesy($image);
        $flipped = imagecreatetruecolor($w, $h);
        if ($mode) {
            for ($y = 0; $y < $h; $y++) {
                imagecopy($flipped, $image, 0, $y, 0, $h - $y - 1, $w, 1);
            }
        } else {
            for ($x = 0; $x < $w; $x++) {
                imagecopy($flipped, $image, $x, 0, $w - $x - 1, 0, 1, $h);
            }
        }
        return $flipped;    	
    }
}
?>