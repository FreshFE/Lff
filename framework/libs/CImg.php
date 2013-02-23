<?php
/**
 *autor: cuity@20120906
 *func:  图片处理类
 *
*/
class CImg
{
    function __construct($cfgArr=array())
    {
        // count($cfgArr)>0 && $this->Init($cfgArr);
    }
    
    /*
    * desc: 裁剪图片
    *
    *
    *
    */
    public static function cutImg($fpimg, $dWidth=250, $dHeight=280, $xArr=array())
    {
        if(!is_file($fpimg)) return false;
        $iArr = self::getImgInfo($fpimg);
        if(false === $iArr) return false;
        
        $scope = isset($xArr['scope'])?$xArr['scope']:true;
        $suff  = isset($xArr['suff'])?$xArr['suff']:'zoom';  
        
        $graphThumbl = imagecreatetruecolor($dWidth, $dHeight);
        $graphOrigin = imagecreatefrompng($fpimg);
        $rgbblack = imagecolorallocate($graphThumbl, 0, 0, 0);
        imagecolortransparent($graphThumbl, $rgbblack);
        
        $srcWidth  = imagesx($graphOrigin);
        $srcHeight = imagesy($graphOrigin);
        
        if($srcWidth < $dWidth && $srcHeight < $dHeight) {
            //原图比将要截的缩略图小
            $dstWidth  = $srcWidth;
            $dstHeight = $srcHeight;
        }else {
            $divW = $srcWidth  / $dWidth;   //长和宽的比值
            $divH = $srcHeight / $dHeight;  //长和宽的比值
            // $scope = true; //标识了被截剪后的图片是否保持全景
            if($scope) {
                if($divW >= $divH) { //表示为横图(在y方向需要被白)
                    $dstWidth  = $dWidth;
                    $dstHeight = ($srcHeight*$dWidth)/$srcWidth;
                }else {//表示为竖图(在x方向需要被白)
                    $dstWidth  = ($srcWidth*$dHeight)/$srcHeight;
                    $dstHeight = $dHeight;
                }
            }else { //生成的缩略图布满整个图片(这意味着原图可能被截剪)
                if($divW >= $divH) { //表示为横图(在y方向需要被白)
                    $dstWidth  = ($srcWidth*$dHeight)/$srcHeight;
                    $dstHeight = $dHeight;
                }else {//表示为竖图(在x方向需要被白)
                    $dstWidth  = $dWidth;
                    $dstHeight = ($srcHeight*$dWidth)/$srcWidth;
                }
            }
        }
        $dstX = $dstY = 0;
        if($dstHeight < $dHeight) {
            $dstY = ($dHeight-$dstHeight)/2;
        }
        if($dstWidth < $dWidth) {
            $dstX = ($dWidth-$dstWidth)/2;
        }
        
        imagecopyresized($graphThumbl, $graphOrigin, $dstX, $dstY, 0, 0, $dstWidth, $dstHeight, $srcWidth, $srcHeight);

        $name = self::getName($fpimg) . '_'.$suff.'.png';
        if(imagepng($graphThumbl, $name)) {
            return $name;
        }
        return false;
        // imagecopyresized($graphThumbl, $graphOrigin, 
    }
    
    /*
    * desc: img转换为png
    *
    *@$fpjpg  --- string(被转换图片的地址)
    *@fpout   --- png的输出地址(黑夜与fpjpg同目录)
    * return: true if success or else false
    */
    public static function img2png($fpimg, $fpout=null)
    {
        if(!is_file($fpimg)) return false;
        $iArr = self::getImgInfo($fpimg);
        if(false === $iArr) return false;
        // $graphPng = imagecreate($iArr['width'], $iArr['height']);
        $type = $iArr['type'];
        switch($type) {
            case IMAGETYPE_GIF     :
                $graphOld = imagecreatefromgif($fpimg);
                break;
            case IMAGETYPE_JPEG    :
                $graphOld = imagecreatefromjpeg($fpimg);
                break;
            case IMAGETYPE_PNG     :
                return true;
            case IMAGETYPE_SWF     :
                break;
            case IMAGETYPE_PSD     :
                break;
            case IMAGETYPE_BMP     :
                $graphOld = imagecreatefromwbmp($fpimg);
                break;
            case IMAGETYPE_TIFF_II :
                break;
            case IMAGETYPE_TIFF_MM :
                break;
            case IMAGETYPE_JPC     :
                break;
            case IMAGETYPE_JP2     :
                break;
            case IMAGETYPE_JPX     :
                break;
            case IMAGETYPE_JB2     :
                break;
            case IMAGETYPE_SWC     :
                break;
            case IMAGETYPE_IFF     :
                break;
            case IMAGETYPE_WBMP    :
                break;
            case IMAGETYPE_XBM     :
                break;
        }
        if(null === $fpout) {
            $name = self::getName($fpimg);
            echo $fpout = dirname($fpimg) . '/' . $name. '.png';
        }
        return imagepng($graphOld, $fpout);
    }
    
    //获取除后轰名的文件名
    public static function getName($filename)
    {
        $pos = strrpos($filename, '.'); 
        if($pos === false){
            return $filename; // no extension 
        }else { 
            $basename = substr($filename, 0, $pos); 
            $extension = substr($filename, $pos+1); 
            return $basename; 
        }
    }
    public static function getImgInfo($fpimg)
    {   
        if(!is_file($fpimg)) return false;
        $infoArr = array();
        $_t = $infoArr['type'] = exif_imagetype($fpimg);
        if(false === $_t) return false;
        
        if(IMAGETYPE_GIF == $_t || IMAGETYPE_JPEG == $_t) {
            $infoArr['head']  = exif_read_data($fpimg); //只支持gif/jpg两种格式
            $infoArr['width']  = $infoArr['head']['COMPUTED']['Width'];
            $infoArr['height'] = $infoArr['head']['COMPUTED']['Height'];
        }else {
            $arr = getimagesize($fpimg);
            $infoArr['width']  = $arr[0];
            $infoArr['height'] = $arr[1];
        }
        // print_r($infoArr);
        return $infoArr;
        /*
        1  IMAGETYPE_GIF 
        2  IMAGETYPE_JPEG 
        3  IMAGETYPE_PNG 
        4  IMAGETYPE_SWF 
        5  IMAGETYPE_PSD 
        6  IMAGETYPE_BMP 
        7  IMAGETYPE_TIFF_II（Intel 字节顺序） 
        8  IMAGETYPE_TIFF_MM（Motorola 字节顺序）  
        9  IMAGETYPE_JPC 
        10 IMAGETYPE_JP2 
        11 IMAGETYPE_JPX 
        12 IMAGETYPE_JB2 
        13 IMAGETYPE_SWC 
        14 IMAGETYPE_IFF 
        15 IMAGETYPE_WBMP 
        16 IMAGETYPE_XBM 
        */
    }
};

// $Img = new CImg();
CImg::cutImg('b.png');
// $Img->img2png('b.jpeg');
// CImg::getImgInfo('b.png');
?>