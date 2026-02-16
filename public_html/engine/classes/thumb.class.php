<?php
/*
=====================================================
 DataLife Engine - by SoftNews Media Group 
-----------------------------------------------------
 https://dle-news.ru/
-----------------------------------------------------
 Copyright (c) 2004-2025 SoftNews Media Group
=====================================================
 This code is protected by copyright
=====================================================
 File: thumb.class.php
-----------------------------------------------------
 Use: Thumbnail class
=====================================================
*/
use Intervention\Image\ImageManagerStatic as Image;

if( !defined( 'DATALIFEENGINE' ) ) {
	header( "HTTP/1.1 403 Forbidden" );
	header ( 'Location: ../../' );
	die( "Hacking attempt!" );
}

class thumbnail {
	
	public $width;
	public $height;
	public $quality = 90;
	public $re_save = false;
	public $format = '';

	private $file = '';	
	private $diver = 'gd';
	private $backup = '';
	private $image;
	private $watermarkimage;
	private $watermark = false;
	private $hidpiwatermarkimage;
	private $hidpiwatermark = false;

	public $tinypng = false;
	public $tinypng_method = false;
	public $tinypng_resize = false;
	public $tinypng_error = false;
	public $tinypng_width = 0;
	public $tinypng_height = 0;
	
	public $error = false;

	function __construct( $file, $backup = false, $min_uploads = false) {
		global $lang, $config;
		
		if( is_array($file) ) {
			
			$this->file = $file['tmp_name'];
			$file_parts = pathinfo($file['name']);
			
		} else {
			
			$this->file = $file;
			$file_parts = pathinfo($file);
		}

		$this->backup  = $backup;
		$this->quality = $config['jpeg_quality'];

		try {

			if($config['image_driver'] != "2") {
				
				if(extension_loaded('imagick') && class_exists('Imagick'))	{
					
					$this->diver  =  'imagick';
					
					if ( ! \Imagick::queryFormats('WEBP') AND function_exists('imagewebp') AND $config['image_driver'] != "1" ) {
						
						$this->diver  =  'gd';
					
					}
		
				}
				
			}
			
			Image::configure(array('driver' => $this->diver));
			$this->image = Image::make($this->file)->orientate();
			
			if( $this->backup ) {
				$this->image->backup();
			}

		
		} catch(Exception $e) {
			
			$message = $e->getMessage();

			if( stripos($message, "Unsupported image type" ) !== false OR stripos($message, "Unable to read image" ) !== false ) $message = $lang['file_not_image'];
			
			$this->error( $message );
			return false;

		}

		$this->width = $this->image->width();
		$this->height = $this->image->height();
		$mime = $this->image->mime();

		switch ($mime) {
            case 'image/png':
            case 'image/x-png':
				$this->format = "png";
				break;
			case 'image/gif':
				$this->format = "gif";
				break;
			case 'image/avif':
			case 'image/heif':
				$this->format = "avif";
				break;
            case 'image/webp':
            case 'image/x-webp':
			case 'image/heic':
			case 'image/heic-sequence':
				$this->format = "webp";
				break;
			default:
				$this->format = "jpg";
		}

		$file_parts['extension'] = isset($file_parts['extension']) ? $file_parts['extension'] : '';
		
		if($file_parts['extension'] != $this->format) {
			
			$this->re_save = true;
			
		}
		
		if( $config['force_webp'] AND $this->format != $config['force_webp'] ) {
			$this->re_save = true;
			$this->format = $config['force_webp'];
		}
		
		if( intval( $config['min_up_side'] ) AND $min_uploads) {

			$min_size = explode ("x", $config['min_up_side']);
			
			$allowed = true;
			
			if ( count($min_size) == 2 ) {
				
				$min_size[0] = intval($min_size[0]);
				$min_size[1] = intval($min_size[1]);
	
				if( $this->width < $min_size[0] OR $this->height < $min_size[1] ) {

					$allowed = false;
				
				}
				
			} else {
				
				$min_size[0] = intval($min_size[0]);
				
				if( $this->width < $min_size[0] OR $this->height < $min_size[0] ) {
					
					$allowed = false;
				
				}
				
			}
			
			if( !$allowed ) {
				
				$lang['upload_error_7'] = str_ireplace("{minsize}", $config['min_up_side'], $lang['upload_error_7']);
				
				$this->error( $lang['upload_error_7'] );
				return false;
				
			}
		
		}
		
		if( $config['image_tinypng'] AND $config['tinypng_key'] AND ($this->format == "png" OR $this->format == "jpg" OR $this->format == "webp") ) {
			
			try {
				
				\Tinify\setKey( $config['tinypng_key'] );
				
				$this->tinypng = true;
				$this->tinypng_method = false;
				$this->tinypng_resize = $config['tinypng_resize'];
				$this->re_save = true;
				
			} catch(\Tinify\Exception $e) {
			
				$this->tinypng = false;
				$this->tinypng_error = $e->getMessage();
			}
			
		}

		
	}
	
	function size_auto($size = 100, $site = 0, $hidpi = false) {
		
		if( $this->error ) return false;

		$size = explode ("x", $size);

		if ( count($size) == 2 ) {
			
			$size[0] = intval($size[0]);
			$size[1] = intval($size[1]);

			if ( $size[0] < 10 ) return false;
			if ( $size[1] < 10 ) return false;

			return $this->crop( $size[0], $size[1], $hidpi );

		} else {
			
			$size[0] = intval($size[0]);

			if ( $size[0] < 10 ) return false;

			return $this->scale( $size[0], $site, $hidpi);

		}

	}

	function crop($nw , $nh, $hidpi = false) {
		
		if( $this->error ) return false;

		if ($hidpi) {
			$nw = $nw * 2;
			$nh = $nh * 2;
		}

		if( $this->width <= $nw AND $this->height <= $nh ) {
			return false;
		}

		if( $this->tinypng AND $this->tinypng_resize ) {
			
			$this->tinypng_method = "cover";
			$this->tinypng_width = $nw;
			$this->tinypng_height = $nh;
			
		}

		try {
			
			$this->image->fit($nw, $nh, function ($constraint) {
				$constraint->upsize();
			});
			
			$this->re_save = true;
		
		} catch(Exception $e) {
			
			$this->error( $e->getMessage() );
			return false;

		}
		
		$this->width = $this->image->width();
		$this->height = $this->image->height();
		
		return true;
	}

	function scale($size = 100, $site = 0, $hidpi = false) {
		
		if( $this->error ) return false;

		$site = intval( $site );
		$size = intval( $size );

		if($hidpi) $size = $size * 2;

		if( $this->width <= $size AND $this->height <= $size ) {
			return false;
		}
		
		switch ($site) {
			
			case "1" :
				
				if( $this->width <= $size ) {
					
					return false;
				
				} else {
					
					try {
						
						$this->image->widen($size, function ($constraint) {
							$constraint->upsize();
						});
					
					} catch(Exception $e) {
						
						$this->error( $e->getMessage() );
						return false;
			
					}
		
				}
				
				break;
			
			case "2" :
				
				if( $this->height <= $size ) {
					
					return false;
				
				} else {
					
					try {
						
						$this->image->heighten($size, function ($constraint) {
							$constraint->upsize();
						});
					
					} catch(Exception $e) {
						
						$this->error( $e->getMessage() );
						return false;
			
					}

					
				}
				
				break;
			
			default :
				
				if( $this->width >= $this->height ) {
					
					try {
						
						$this->image->resize($size, null, function ($constraint) {
							$constraint->aspectRatio();
							$constraint->upsize();
						});
					
					} catch(Exception $e) {
						
						$this->error( $e->getMessage() );
						return false;
			
					}
					
					
				} else {
					
					try {
						
						$this->image->resize(null, $size, function ($constraint) {
							$constraint->aspectRatio();
							$constraint->upsize();
						});
					
					} catch(Exception $e) {
						
						$this->error( $e->getMessage() );
						return false;
			
					}
				
				}
				
				break;
		}
		
		$this->width = $this->image->width();
		$this->height = $this->image->height();
		$this->re_save = true;
		
		return true;

	}

	private function detect_color ( $temp_x, $temp_y, $position ) {

		$temp_img = Image::make($this->file);
		$temp_img->resizeCanvas($temp_x, $temp_y, $position);

		$width = $temp_img->width();
		$height = $temp_img->height();
		$sum = 0;

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$rgb = $temp_img->pickColor($x, $y);
				$luminance = 0.2126 *  $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
				$sum += $luminance;
			}
		}
		unset($temp_img);
		
		$average = $sum / ($width * $height);
		$threshold = 128;

		if ($average < $threshold) {
			return true;
		} else {
			return false;
		}

	}

	function insert_watermark($min_image, $hidpi = false) {
		global $config, $lang;
		
		if( $this->error ) return false;
		
		$margin = 10;
		$min_image = intval($min_image);

		if (!$config['watermark_type']) $hidpi = false;

		if ($hidpi) {
			$margin = $margin * 2;
			$min_image = $min_image * 2;
		}

		$watermark_image_light = 'watermark_light.png';
		$watermark_image_dark = 'watermark_dark.png';

		if($config['watermark_seite'] == 1) {
			
			$position = 'top-left';
			
		} elseif($config['watermark_seite'] == 2) {
			
			$position = 'top-right';
			
		} elseif($config['watermark_seite'] == 3) {
			
			$position = 'bottom-left';
			
		} elseif($config['watermark_seite'] == 4) {
			
			$position = 'bottom-right';
			
		} else {
	
			$position = 'center';
			$margin = 0;
			
		}

		$make_watermark = false;

		if(!$hidpi AND !$this->watermark) $make_watermark = true;

		if ($hidpi AND !$this->hidpiwatermark) $make_watermark = true;

		if( ( $make_watermark ) ) {

			if( !$config['watermark_type'] ) {

				try {

					list($temp_x, $temp_y) = getimagesize(ROOT_DIR . '/templates/' . $config['skin'] . '/dleimages/' . $watermark_image_dark);
					$lightness = $this->detect_color($temp_x, $temp_y, $position);
					
					$watermark_image = $lightness ? $watermark_image_light : $watermark_image_dark;

					$this->watermarkimage = Image::make( ROOT_DIR . '/templates/' . $config['skin'] . '/dleimages/'. $watermark_image );
					
				} catch(Exception $e) {
					
					$lang['images_uperr_5'] = str_ireplace('{file}', '/templates/' . $config['skin'] . '/dleimages/'. $watermark_image_light, $lang['images_uperr_5']); 
					$this->error( $lang['images_uperr_5'] );
					return false;
		
				}
				
			} else {
				
				try {

					$watermark_size = intval($config['watermark_font']);

					if ($hidpi) {
						$watermark_size = $watermark_size * 2;
					}

					$fontclassname = sprintf('\Intervention\Image\%s\Font', $this->image->getDriver()->getDriverName());
					$font = new $fontclassname($config['watermark_text']);
					$font->file(ENGINE_DIR . '/skins/fonts/verdana.ttf');
					$font->size( $watermark_size );
					$w_sizes = $font->getBoxSize();
					
					$lightness = $this->detect_color($w_sizes['width'], $w_sizes['height'], $position);
					
					$watermark_color = $lightness ? $config['watermark_color_light'] : $config['watermark_color_dark'];

					if ( $hidpi ) {

						$this->hidpiwatermarkimage = Image::canvas($w_sizes['width'], $w_sizes['height']);

						$this->hidpiwatermarkimage->text($config['watermark_text'], 0, 0, function ($font) use ($watermark_color, $watermark_size) {
							global $config;

							$font->file(ENGINE_DIR . '/skins/fonts/verdana.ttf');
							$font->size($watermark_size);
							$font->valign('top');
							$font->color($watermark_color);
						});

					} else {

						$this->watermarkimage = Image::canvas($w_sizes['width'], $w_sizes['height']);

						$this->watermarkimage->text($config['watermark_text'], 0, 0, function ($font) use ($watermark_color, $watermark_size) {
							global $config;

							$font->file(ENGINE_DIR . '/skins/fonts/verdana.ttf');
							$font->size( $watermark_size );
							$font->valign('top');
							$font->color($watermark_color);
						});

					}

					
				} catch(Exception $e) {
					
					$this->error( $lang['images_uperr_6'] );
					return false;
		
				}
				
			}

			try {
				
				$config['watermark_rotate'] = intval($config['watermark_rotate']);
				$config['watermark_opacity'] = intval($config['watermark_opacity']);
				
				if($config['watermark_opacity'] < 0 OR $config['watermark_opacity'] > 100 ) {
					$config['watermark_opacity'] = 100;
				}

				if( $config['watermark_rotate'] ) {

					if ($hidpi) {
						$this->hidpiwatermarkimage->rotate($config['watermark_rotate']);
					} else {
						$this->watermarkimage->rotate($config['watermark_rotate']);
					}

				}
				
				if( $config['watermark_opacity'] != 100 ) {

					if ($hidpi) {
						$this->hidpiwatermarkimage->opacity($config['watermark_opacity']);
					} else {
						$this->watermarkimage->opacity($config['watermark_opacity']);
					}
				}
				
			} catch(Exception $e) {
				
				$this->error( $lang['images_uperr_6'] );
				return false;
	
			}

			$this->watermark = true;

		}

		if ($hidpi) {
			$watermark_width = $this->hidpiwatermarkimage->width() + $margin;
			$watermark_height = $this->hidpiwatermarkimage->height() + $margin;
		} else {
			$watermark_width = $this->watermarkimage->width() + $margin;
			$watermark_height = $this->watermarkimage->height() + $margin;
		}

		if( $this->width < $min_image OR $this->height < $min_image OR $this->width < $watermark_width OR $this->height < $watermark_height ) {
			
			return false;
		}
		
		try {

			if ($hidpi) {
				$this->image->insert($this->hidpiwatermarkimage, $position, $margin, $margin);
			} else {
				$this->image->insert($this->watermarkimage, $position, $margin, $margin);
			}

			$this->re_save = true;
			
		} catch(Exception $e) {
			
			$this->error( $e->getMessage() );
			return false;

		}
		
		return true;
	
	}

	function save($save = "", $autoprefix = false) {
		global $config;

		if( $this->error ) return false;

		$file_parts = pathinfo($save);

		if( isset( $file_parts['dirname'] ) AND $file_parts['dirname'] ) {
			
			$save_path = $file_parts['dirname'].'/';
			
		} else $save_path = '';
		
		if( isset( $file_parts['filename'] ) AND $file_parts['filename'] ) {
			
			$file_name = $file_parts['filename'].'.'.$this->format;
			
		} else $file_name = UniqIDReal().'.'.$this->format;
		
		if( $autoprefix ) {
			
			if( (DLEFiles::FileExists( $save_path.$file_name ) AND !$config['images_uniqid']) OR  $config['images_uniqid'] ) {
				$file_name = UniqIDReal()."_".$file_name;
			}
			
		}

		try {
			
			$imagesource = (string) $this->image->encode($this->format, $this->quality);
			
			if( $this->backup ) {
				
				$this->image->reset();
				$this->width = $this->image->width();
				$this->height = $this->image->height();
				
			}
			
		} catch(Exception $e) {
			
			$this->error( $e->getMessage() );
			return false;
	
		}
		
		if( $this->tinypng ) {

			$imagesource = $this->tinypng_compress( $imagesource );
			
			
		}
		
		if( !DLEFiles::Save( $save_path.$file_name,  $imagesource ) ) {
			$this->error( DLEFiles::$error );
			return false;
		}

		return $file_name;

	}
	
	function tinypng_compress( $imagesource ) {
	
		if( $this->error ) return false;
		
		try {
			
			if( $this->tinypng_method ) {
				
				if( stripos($this->file, "https://" ) === 0 OR stripos($this->file, "http://" ) === 0 ) {
					$source = \Tinify\fromUrl( $this->file );
				} else {
					$source = \Tinify\fromFile( $this->file );
				}

				
				$options = array("method" => $this->tinypng_method);
				
				if( $this->tinypng_width ) $options['width'] = $this->tinypng_width;
				if( $this->tinypng_height ) $options['height'] = $this->tinypng_height;
			
				$resized = $source->resize($options);
				$tinypng_buffer = $resized->toBuffer();

			} else {
				
				$source = \Tinify\fromBuffer( $imagesource );
				$tinypng_buffer = $source->toBuffer();
				
			}
			
			return $tinypng_buffer;
			
		} catch(\Tinify\Exception $e) {
			
			$this->tinypng = false;
		
			$this->tinypng_error = $e->getMessage();
			
			return $imagesource;
			
		}

	}

	function error( $text ) {
		
		$this->error = (string)$text;
		
	}
	
}
