<?php
include_once AuWebRoot.'/src/back/import/import.php';

Class ImageEditor
{
    // *** Class variables
    private $imageBase64;
    private $image;
    private $width;
    private $height;
    private $imageResized;
    private $fileName;

    function __construct($fileName)
    {
        // *** Open up the file
        $this->fileName = $fileName;
        $this->image = $this->openImage($this->fileName);

        // *** Get width and height
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    public static function newImageBase64($base64)
    {
        $tmpDir = FileUtils::getTmpDir();
        $imageExtension = Utils::getImageExtensionFromBase64($base64);
        $imageName = Utils::getRandomString() . '.' . $imageExtension;
        $path = FileUtils::buildPath($tmpDir, $imageName);
        $base64PrefixLess = Utils::extractBase64($base64);
        FileUtils::createDir($tmpDir);
        chmod($tmpDir, 0777);
        $file = file_put_contents($path, base64_decode($base64PrefixLess));
        chmod($path, 0777);
        if ($file == true) {
            return new ImageEditor($path);
        }
        return null;
    }

    public static function newImageFromBinary($binaryImage, $imageExtension)
    {
        $tmpDir = FileUtils::getTmpDir();
        $imageName = Utils::getRandomString() . '.' . $imageExtension;
        $path = FileUtils::buildPath($tmpDir, $imageName);
        FileUtils::createDir($tmpDir);
        //chmod($tmpDir, 0777);
        $file = file_put_contents($path, $binaryImage);
        //chmod($path, 0777);
        if ($file == true) {
            return new ImageEditor($path);
        }
        $error = error_get_last();
        throw new InternalError($error['message']);
    }

    private function openImage($file)
    {
        // *** Get extension
        $extension = strtolower(strrchr($file, '.'));
        switch ($extension) {
            case '.JPG':
            case '.JPEG':
            case '.jpg':
            case '.jpeg':
                $img = imagecreatefromjpeg($file);
                break;
            case '.GIF':
            case '.gif':
                $img = imagecreatefromgif($file);
                break;
            case '.png':
            case '.PNG':
                $img = imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }
        if ($img == false) {
            $error = error_get_last();
            throw new InternalError($error['message']);
        }
        return $img;
    }

    public function getImagePath() {
        return $this->fileName;
    }

    public function resizeImage($newWidth, $newHeight, $option = "auto")
    {

        // *** Get optimal width and height - based on $option
        $optionArray = $this->getDimensions($newWidth, $newHeight, strtolower($option));

        $optimalWidth = $optionArray['optimalWidth'];
        $optimalHeight = $optionArray['optimalHeight'];

        // *** Resample - create image canvas of x, y size
        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);
        $this->width = $optimalWidth;
        $this->height = $optimalHeight;
        // *** if option is 'crop', then crop too
        $this->image = $this->imageResized;
        if ($option == 'crop') {
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }
    }

    private function getDimensions($newWidth, $newHeight, $option)
    {

        switch ($option) {
            case 'exact':
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    private function getSizeByFixedHeight($newHeight)
    {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    private function getSizeByFixedWidth($newWidth)
    {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    private function getSizeByAuto($newWidth, $newHeight)
    {
        if ($this->height < $this->width) {
            // *** Image to be resized is wider (landscape)
            $optimalWidth = $newWidth;
            $optimalHeight = $this->getSizeByFixedWidth($newWidth);
        } elseif ($this->height > $this->width) {
            // *** Image to be resized is taller (portrait)
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight = $newHeight;
        } else {
            // *** Image to be resizerd is a square
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            } else {
                // *** Sqaure being resized to a square
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            }
        }

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    private function getOptimalCrop($newWidth, $newHeight)
    {

        $heightRatio = $this->height / $newHeight;
        $widthRatio = $this->width / $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth = $this->width / $optimalRatio;

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
    {
        // *** Find center - this will be used for the crop
        $cropStartX = ($optimalWidth / 2) - ($newWidth / 2);
        $cropStartY = ($optimalHeight / 2) - ($newHeight / 2);

        $crop = $this->image;
        //imagedestroy($this->imageResized);

        // *** Now crop from center to exact requested size
        $this->image = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($this->image, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);
    }

    public function applyWatermark($watermarkPath)
    {

        // Determine watermark size and type
        $wsize = getimagesize($watermarkPath);
        $watermark_x = $wsize[0];
        $watermark_y = $wsize[1];
        $watermark_type = $wsize[2]; // 1 = GIF, 2 = JPG, 3 = PNG

        // load watermark
        $watermark = $this->openImage($watermarkPath);

        // where do we put watermark on the image?
        $dest_x = 0;/*$this->width - $watermark_x - $this->offset_x*/;
        $dest_y = 0;/*$this->height - $watermark_y - $this->offset_y*/;

        $res = imagecopy($this->image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_x, $watermark_y);
        imagedestroy($watermark);
    }

    public function saveImage($savePath, $imageQuality = "100")
    {
        // *** Get extension
        $extension = strrchr($savePath, '.');
        $extension = strtolower($extension);
        if (strlen($extension) == 0 || $extension == '.') {
            $extension = strrchr($this->fileName, '.');
            $extension = strtolower($extension);
            $savePath .= $extension;
        }

        switch ($extension) {
            case '.JPG':
            case '.JPEG':
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->image, $savePath, $imageQuality);
                }
                break;

            case '.GIF':
            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->image, $savePath);
                }
                break;

            case '.PNG':
            case '.png':
                // *** Scale quality from 0-100 to 0-9
                $scaleQuality = round(($imageQuality / 100) * 9);

                // *** Invert quality setting as 0 is best, not 9
                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                    imagepng($this->image, $savePath, $invertScaleQuality);
                }
                break;

            // ... etc

            default:
                // *** No extension - No save.
                return false;
                break;
        }
        imagedestroy($this->image);
        return true;
    }

    /**
     * @param mixed $imageBase64
     */
    public function setImageBase64($imageBase64)
    {
        $this->imageBase64 = $imageBase64;
    }

    /**
     * @return mixed
     */
    public function getImageBase64()
    {
        return $this->imageBase64;
    }
}