<?php

namespace Bootstrap\Libs;

/*
* Create Thumbnail For Uploads
*/

class Thumbnail
{
    public static $_instance;

    private $imageLocation, $sourceImage, $destImage;

    // New a class object
    public static function create()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    // Generate the image
    public function image($path = '')
    {
        $this->imageLocation = $path;
        return $this;
    }

    // Create the thumbnail
    public function thumbnail($width = 1024)
    {
        if (file_exists($this->imageLocation)) {
            $imageSize = getimagesize($this->imageLocation);
            $height = floor(($width * $imageSize[1]) / $imageSize[0]);
            $this->destImage = imagecreatetruecolor($width, $height);

            if (exif_imagetype($this->imageLocation) == IMAGETYPE_JPEG) {
                $this->sourceImage = imagecreatefromjpeg($this->imageLocation);
            } else if (exif_imagetype($this->imageLocation) == IMAGETYPE_GIF) {
                $this->sourceImage = imagecreatefromgif($this->imageLocation);
            } else if (exif_imagetype($this->imageLocation) == IMAGETYPE_PNG) {
                $this->sourceImage = imagecreatefrompng($this->imageLocation);
                $background = imagecolorallocate($this->destImage, 255, 255, 255);
                imagecolortransparent($this->destImage, $background);
                imagealphablending($this->destImage, false);
                imagesavealpha($this->destImage, true);
            }

            imagecopyresampled($this->destImage, $this->sourceImage, 0, 0, 0, 0, $width, $height, $imageSize[0], $imageSize[1]);
        }
        return $this;
    }

    // Save image to somewhere
    public function to($imageDestLocation = '')
    {
        if (empty($this->destImage)) {
            return false;
        }

        $imageDestLocation = $imageDestLocation;

        if (exif_imagetype($this->imageLocation) == IMAGETYPE_JPEG) {
            imagejpeg($this->destImage, $imageDestLocation);
        } else if (exif_imagetype($this->imageLocation) == IMAGETYPE_GIF) {
            imagegif($this->destImage, $imageDestLocation);
        } else if (exif_imagetype($this->imageLocation) == IMAGETYPE_PNG) {
            imagepng($this->destImage, $imageDestLocation);
        }
        return true;
    }

    // Upload Image - torrent/edit
    public function uploadimage($x, $imgname, $tid)
    {
        $imagesdir = IMAGE_PATH;
        $allowed_types = CONFIG['ALLOWEDIMAGETYPES'];
        if (!($_FILES["image$x"]["name"] == "")) {
            if ($imgname != "") {
                $img = "$imagesdir/$imgname";
                unlink($img);
            }
            $y = $x + 1;
            $im = getimagesize($_FILES["image$x"]["tmp_name"]);
            if (!$im[2]) {
                showMessage((URLROOT . $_SERVER["HTTP_REFERER"]), "Invalid Image $y.");
            }
            if (!array_key_exists($im['mime'], $allowed_types)) {
                showMessage((URLROOT . $_SERVER["HTTP_REFERER"]), LANG["INVALID_FILETYPE_IMAGE"]);
            }
            if ($_FILES["image$x"]["size"] > CONFIG['IMAGEMAXFILESIZE']) {
                showMessage((URLROOT . $_SERVER["HTTP_REFERER"]), sprintf(LANG["INVAILD_FILE_SIZE_IMAGE"], $y));
            }
            $uploaddir = "$imagesdir/";
            $ifilename = $tid . $x . $allowed_types[$im['mime']];
            $this->copyImage($_FILES["image$x"]["tmp_name"], $uploaddir . $ifilename);
            return $ifilename;
        }
    }

    // Save Image
    public function copyImage($source, $destination, $redirect = URLROOT)
    {
        // compress size max 100
        $compression_quality = 25;
        // set type
        $type = substr($destination, -3);
        $jpeg = substr($destination, -4);
        // set destination
        $new_destination = substr($destination, 0, -3) . 'webp';

        if ($type == 'png') {
            $image = imagecreatefrompng($source);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        } elseif ($type == 'jpg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($type == 'gif') {
            $image = imagecreatefromgif($source);
            imagepalettetotruecolor($image);
        } elseif ($jpeg == 'jpeg') {
            $image = imagecreatefromjpeg($source);
            $new_destination = substr($destination, 0, -4) . 'webp';
        } else {
            showMessage(URLROOT, 'Strange Image Type - Only Gif, Png, Jpeg & Jpg allowed');
        }

        // convert image
        $result = imagewebp($image, $new_destination, $compression_quality);
        // check result
        if (false === $result) {
            showMessage(URLROOT, sprintf(LANG["No Image Result"]));
        }
        // destroy temp
        imagedestroy($image);
        return $new_destination;
    }
}