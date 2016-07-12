<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees;

use Fisharebest\Webtrees\Config;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsMedia;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Media;
use MyArtJaub\Webtrees\Functions\Functions;

/**
 * Image builder for Media object.
 */
class ImageBuilder {
    
    /**
     * Reference media
     * @var Media $media
     */
    protected $media;
    
    /**
     * Use TTF font
     * @var bool $use_ttf
     */
    protected $use_ttf;
    
    /**
     * Expiration offset. Default is one day.
     * @var int $expire_offset
     */
    protected $expire_offset;
   
    /**
     * Should the certificate display a watermark
     * @var bool $show_watermark
     */
    protected $show_watermark;
        
    /**
     * Maximum watermark font size. Default is 18.
     * @var int $font_max_size
     */
    protected $font_max_size;
    
    /**
     * Watermark font color, in hexadecimal. Default is #4D6DF3.
     * @var string $font_color
     */
    protected $font_color;
    
	/**
	* Contructor for ImageBuilder
	*
	* @param Media|null $media Reference media object
	*/
	public function __construct(Media $media = null){
	    $this->media = $media;
	    $this->use_ttf = function_exists('imagettftext');
	    $this->expire_offset = 3600 * 24;
	    $this->show_watermark = true;
	    $this->font_max_size = 18;
	    $this->font_color = '#4D6DF3';
	}
	
	/**
	 * Get the expiration offset.
	 * 
	 * @return int
	 */
	public function getExpireOffset() {
	    return $this->expire_offset;
	}
	
	/**
	 * Set the expiration offset.
	 * 
	 * @param int $expireOffset
	 * @return ImageBuilder
	 */
	public function setExpireOffset($expireOffset) {
	    if($expireOffset) $this->expire_offset = $expireOffset;
	    return $this;
	}
	
	/**
	 * Gets whether the watermark should be shown.
	 * 
	 * @return bool
	 */
	public function isShowWatermark() {
	    return $this->show_watermark;
	}
	
	/**
	 * Set whether the watermark should be shown.
	 * 
	 * @param bool $show_watermark
	 * @return ImageBuilder
	 */
	public function setShowWatermark($show_watermark) {
	    if(!is_null($show_watermark)) $this->show_watermark = $show_watermark;
	    return $this;
	}
	
	/**
	 * Set the watermark maximum font size.
	 * 
	 * @param int $font_max_size
	 * @return ImageBuilder
	 */
	public function setFontMaxSize($font_max_size) {
	    if($font_max_size) $this->font_max_size = $font_max_size;
	    return $this;
	}
	
	/**
	 * Set the watermark font color
	 * 
	 * @param int $font_color
	 * @return ImageBuilder
	 */
	public function setFontColor($font_color) {
	    if($font_color) $this->font_color = $font_color;
	    return $this;
	}
	
	/**
	 * Render the image to the output.
	 */
	public function render(){
	    
	    if (!$this->media || !$this->media->canShow()) {
	        Log::addMediaLog('Image Builder error: >' . I18N::translate('Missing or private media object.'));
	        $this->renderError();
	    }
	    
	    $serverFilename = $this->media->getServerFilename();
	    
	    if (!file_exists($serverFilename)) {
	        Log::addMediaLog('Image Builder error: >'. I18N::translate('The media object does not exist.').'< for path >'.$serverFilename.'<');
	        $this->renderError();
	    }
	    
	    $mimetype = $this->media->mimeType();
	    $imgsize = $this->media->getImageAttributes();
	    $filetime = $this->media->getFiletime();
	    $filetimeHeader = gmdate('D, d M Y H:i:s', $filetime) . ' GMT';	    
	    $expireHeader = gmdate('D, d M Y H:i:s', WT_TIMESTAMP + $this->getExpireOffset()) . ' GMT';
	    
	    $type = Functions::isImageTypeSupported($imgsize['ext']);
	    $usewatermark = false;
	    // if this image supports watermarks and the watermark module is intalled...
	    if ($type) {
	        $usewatermark = $this->isShowWatermark();
	    }
	    
	    // determine whether we have enough memory to watermark this image
	    if ($usewatermark) {
	        if (!FunctionsMedia::hasMemoryForImage($serverFilename)) {
	            // not enough memory to watermark this file
	            $usewatermark = false;
	        }
	    }
	    
	    $etag = $this->media->getEtag();
	    
	    // parse IF_MODIFIED_SINCE header from client
	    $if_modified_since = 'x';
	    if (!empty(Filter::server('HTTP_IF_MODIFIED_SINCE'))) {
	        $if_modified_since = preg_replace('/;.*$/', '', Filter::server('HTTP_IF_MODIFIED_SINCE'));
	    }
	    
	    // parse IF_NONE_MATCH header from client
	    $if_none_match = 'x';
	    if (!empty(Filter::server('HTTP_IF_NONE_MATCH'))) {
	        $if_none_match = str_replace('"', '', Filter::server('HTTP_IF_NONE_MATCH'));
	    }
	    
	    // add caching headers.  allow browser to cache file, but not proxy
	    header('Last-Modified: ' . $filetimeHeader);
	    header('ETag: "' . $etag . '"');
	    header('Expires: ' . $expireHeader);
	    header('Cache-Control: max-age=' . $this->getExpireOffset() . ', s-maxage=0, proxy-revalidate');
	    
	    // if this file is already in the user’s cache, don’t resend it
	    // first check if the if_modified_since param matches
	    if ($if_modified_since === $filetimeHeader) {
	        // then check if the etag matches
	        if ($if_none_match === $etag) {
	            http_response_code(304);
	    
	            return;
	        }
	    }	    

	    // send headers for the image
	    header('Content-Type: ' . $mimetype);
	    header('Content-Disposition: filename="' . addslashes(basename($this->media->getFilename())) . '"');
	     
	    if ($usewatermark) {
	        // generate the watermarked image
	        $imCreateFunc = 'imagecreatefrom' . $type;
	        $imSendFunc   = 'image' . $type;
	    
	        if (function_exists($imCreateFunc) && function_exists($imSendFunc)) {
	            $im = $imCreateFunc($serverFilename);
	            $im = $this->applyWatermark($im);
	    	    
	            // send the image
	            $imSendFunc($im);
	            imagedestroy($im);
	    
	            return;
	        } else {
	            // this image is defective.  log it
	            Log::addMediaLog('Image Builder error: >' . I18N::translate('This media file is broken and cannot be watermarked.') . '< in file >' . $serverFilename . '< memory used: ' . memory_get_usage());
	        }
	    }
	    
	    // determine filesize of image (could be original or watermarked version)
	    $filesize = filesize($serverFilename);
	    
	    // set content-length header, send file
	    header('Content-Length: ' . $filesize);
	    
	    // Some servers disable fpassthru() and readfile()
	    if (function_exists('readfile')) {
	        readfile($serverFilename);
	    } else {
	        $fp = fopen($serverFilename, 'rb');
	        if (function_exists('fpassthru')) {
	            fpassthru($fp);
	        } else {
	            while (!feof($fp)) {
	                echo fread($fp, 65536);
	            }
	        }
	        fclose($fp);
	    }	    
	}
	
	/**
	 * Render an error as an image.
	 */
	protected function renderError() {	
	    $error = I18N::translate('The media file was not found in this family tree.');

    	$width  = (mb_strlen($error) * 6.5 + 50) * 1.15;
    	$height = 60;
    	$im     = imagecreatetruecolor($width, $height); /* Create a black image */
    	$bgc    = imagecolorallocate($im, 255, 255, 255); /* set background color */
    	imagefilledrectangle($im, 2, 2, $width - 4, $height - 4, $bgc); /* create a rectangle, leaving 2 px border */
    
    	$this->embedText($im, $error, 100, '255, 0, 0', WT_ROOT . Config::FONT_DEJAVU_SANS_TTF, 'top', 'left');
    
    	http_response_code(404);
    	header('Content-Type: image/png');
    	imagepng($im);
    	imagedestroy($im);
	}
	
	/**
	 * Returns the entered image with a watermark printed.
	 * Similar to the the media firewall function.
	 *
	 * @param resource $im Certificate image to watermark
	 * @return resource Watermarked image
	 */
	protected function applyWatermark($im) {
	    
	    // text to watermark with	    
	    if(method_exists($this->media, 'getWatermarkText')) {
	       $word1_text = $this->media->getWatermarkText();
	    }
	    else {
	        $word1_text = $this->media->getTitle();
	    }
	
	    $this->embedText(
	        $im, 
	        $word1_text, 
	        $this->font_max_size,
	        $this->font_color,
	        WT_ROOT . Config::FONT_DEJAVU_SANS_TTF,
	        'top', 
	        'left'
	     );
	
	    return ($im);
	}
	
	/**
	 * Embed a text in an image.
	 * Similar to the the media firewall function.
	 *
	 * @param resource $im Image to watermark
	 * @param string $text Text to display
	 * @param int $maxsize Maximum size for the font
	 * @param string $color Font color
	 * @param string $font Font to be used
	 * @param string $vpos Description of the vertical position (top, middle, bottom, accross)
	 * @param string $hpos Description of the horizontal position (right, left, top2bottom, bottom2top)
	 */
	protected function embedText($im, $text, $maxsize, $color, $font, $vpos, $hpos) {
	    
	    // there are two ways to embed text with PHP
	    // (preferred) using GD and FreeType you can embed text using any True Type font
	    // (fall back) if that is not available, you can insert basic monospaced text
	    
	    $col = $this->hexrgb($color);
	    $textcolor = imagecolorallocate($im, $col['red'], $col['green'], $col['blue']);
	    
	    // make adjustments to settings that imagestring and imagestringup can’t handle
	    if (!$this->use_ttf) {
	        // imagestringup only writes up, can’t use top2bottom
	        if ($hpos === 'top2bottom') {
	            $hpos = 'bottom2top';
	        }
	    }
	    
	    $text       = I18N::reverseText($text);
	    $height     = imagesy($im);
	    $width      = imagesx($im);
	    $calc_angle = rad2deg(atan($height / $width));
	    $hypoth     = $height / sin(deg2rad($calc_angle));
	    
	    // vertical and horizontal position of the text
	    switch ($vpos) {
	        default:
	        case 'top':
	            $taille   = $this->textLength($maxsize, $width, $text);
	            $pos_y    = $height * 0.15 + $taille;
	            $pos_x    = $width * 0.15;
	            $rotation = 0;
	            break;
	        case 'middle':
	            $taille   = $this->textLength($maxsize, $width, $text);
	            $pos_y    = ($height + $taille) / 2;
	            $pos_x    = $width * 0.15;
	            $rotation = 0;
	            break;
	        case 'bottom':
	            $taille   = $this->textLength($maxsize, $width, $text);
	            $pos_y    = ($height * .85 - $taille);
	            $pos_x    = $width * 0.15;
	            $rotation = 0;
	            break;
	        case 'across':
	            switch ($hpos) {
	                default:
	                case 'left':
	                    $taille   = $this->textLength($maxsize, $hypoth, $text);
	                    $pos_y    = ($height * .85 - $taille);
	                    $pos_x    = $width * 0.15;
	                    $rotation = $calc_angle;
	                    break;
	                case 'right':
	                    $taille   = $this->textLength($maxsize, $hypoth, $text);
	                    $pos_y    = ($height * .15 - $taille);
	                    $pos_x    = $width * 0.85;
	                    $rotation = $calc_angle + 180;
	                    break;
	                case 'top2bottom':
	                    $taille   = $this->textLength($maxsize, $height, $text);
	                    $pos_y    = ($height * .15 - $taille);
	                    $pos_x    = ($width * .90 - $taille);
	                    $rotation = -90;
	                    break;
	                case 'bottom2top':
	                    $taille   = $this->textLength($maxsize, $height, $text);
	                    $pos_y    = $height * 0.85;
	                    $pos_x    = $width * 0.15;
	                    $rotation = 90;
	                    break;
	            }
	            break;
	    }
	    
	    // apply the text
	    if ($this->use_ttf) {
	        // if imagettftext throws errors, catch them with a custom error handler
	        set_error_handler(array($this, 'imageTtfTextErrorHandler'));
	        imagettftext($im, $taille, $rotation, $pos_x, $pos_y, $textcolor, $font, $text);
	        restore_error_handler();
	    }
	    // Don’t use an ‘else’ here since imagettftextErrorHandler may have changed the value of $useTTF from true to false
	    if (!$this->use_ttf) {
	        if ($rotation !== 90) {
	            imagestring($im, 5, $pos_x, $pos_y, $text, $textcolor);
	        } else {
	            imagestringup($im, 5, $pos_x, $pos_y, $text, $textcolor);
	        }
	    }
	
	}
	
	/**
	 * Convert an hexadecimal color to its RGB equivalent.
	 * 
	 * @param string $hexstr
	 * @return int[]
	 */
	protected function hexrgb ($hexstr)
	{
	    $int = hexdec($hexstr);
	
	    return array('red' => 0xFF & ($int >> 0x10),
	        'green' => 0xFF & ($int >> 0x8),
	        'blue' => 0xFF & $int);
	}
	
    /**
     * Generate an approximate length of text, in pixels.
     *
     * @param int    $t
     * @param int    $mxl
     * @param string $text
     *
     * @return int
     */
    function textLength($t, $mxl, $text) {
    	$taille_c = $t;
    	$len      = mb_strlen($text);
    	while (($taille_c - 2) * $len > $mxl) {
    		$taille_c--;
    		if ($taille_c == 2) {
    			break;
    		}
    	}
    
    	return $taille_c;
    }
    
    /**
     * imagettftext is the function that is most likely to throw an error
     * use this custom error handler to catch and log it
     *
     * @param int    $errno
     * @param string $errstr
     *
     * @return bool
     */
    function imageTtfTextErrorHandler($errno, $errstr) {
        // log the error
        Log::addErrorLog('Image Builder error: >' . $errno . '/' . $errstr . '< while processing file >' . $this->media->getServerFilename() . '<');
    
        // change value of useTTF to false so the fallback watermarking can be used.
        $this->use_ttf = false;
    
        return true;
    }
		
}

?>