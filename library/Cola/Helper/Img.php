<?php
class Cola_Helper_Img {

    public static function resizeImage($image, $width, $height, $scale, $SetW, $SetH) {
        $imginfo = getimagesize($image);
        if ( ! $imginfo )
            throw new Exception('参数错误');
        if ( $imginfo['mime'] == "image/pjpeg" || $imginfo['mime'] == "image/jpeg" ) {
            $source = imagecreatefromjpeg($image);
        } elseif ( $imginfo['mime'] == "image/x-png" || $imginfo['mime'] == "image/png" ) {
            $source = imagecreatefrompng($image);
        } elseif ( $imginfo['mime'] == "image/gif" ) {
            $source = imagecreatefromgif($image);
        }
        if ( ! $source ) {
            throw new Exception('参数错误');
        }
        
        $newImageWidth = ceil($width * $scale);
        $newImageHeight = ceil($height * $scale);
        $newImage = imagecreatetruecolor($SetW, $SetH);
        $color = imagecolorAllocate($newImage, 255, 255, 255);
        imagefill($newImage, 0, 0, $color);
        
        $dx = ($SetW - $newImageWidth) / 2;
        $dy = ($SetH - $newImageHeight) / 2;
        imagecopyresampled($newImage, $source, $dx, $dy, 0, 0, $newImageWidth, $newImageHeight, $width, $height);
        imagejpeg($newImage, $image, 100);
        imagedestroy($newImage);
        chmod($image, 0777);
    }

    /**
     * 批量裁剪图片
     *
     * 优化了$img为url时的性能
     *
     * @see Cola_Helper_Img::cropImage
     */
    public static function cropImgBatch($img, $cropx, $cropy, $cropw, $croph, array $cropedImg) {
        $source = imagecreatefromjpeg($img);
        if ( ! $source ) {
            throw new Exception('参数错误');
        }
        
        foreach ( $cropedImg as $crop ) {
            $newImage = imagecreatetruecolor($crop['width'], $crop['height']);
            imagecopyresampled($newImage, $source, 0, 0, $cropx, $cropy, $crop['width'], $crop['height'], $cropw, $croph);
            imagejpeg($newImage, $crop['file'], 100);
            imagedestroy($newImage);
            chmod($crop['file'], 0777);
        }
    }

    /**
     * 从$img的($cropx,$cropy)开始，裁剪一块尺寸为$cropw*$croph的像素，
     * 填充到尺寸为$cropedImageWidth*$cropedImageHeight的$cropedImg中
     *
     * ($cropx,$cropy)是相对于图片左上角为原点的起始裁剪坐标。
     *
     * 注意，该函数就地修改了$cropedImg，并没有返回值
     *
     * @param string $cropedImg            
     * @param string $img            
     * @param int $cropw            
     * @param int $croph            
     * @param int $cropx            
     * @param int $cropy            
     * @param int $cropedImgWidth            
     * @param int $cropedImgHeight            
     */
    public static function cropImage($img, $cropx, $cropy, $cropw, $croph, $cropedImg, $cropedImgWidth, $cropedImgHeight) {
        $newImage = imagecreatetruecolor($cropedImgWidth, $cropedImgHeight);
        $source = imagecreatefromjpeg($img);
        imagecopyresampled($newImage, $source, 0, 0, $cropx, $cropy, $cropedImgWidth, $cropedImgHeight, $cropw, $croph);
        imagejpeg($newImage, $cropedImg, 100);
        imagedestroy($newImage);
        chmod($cropedImg, 0777);
    }
}