<?php
namespace Laravelladder\Core\Utils\Report;

use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Alignment;
use Illuminate\Support\Facades\Storage;
use Laravelladder\FileSdk\Models\FileMixin;

/**
 * Class Ppt
 * ppt文档生成通用方法
 * @package Laravelladder\Core\Utils\Report
 */
class Ppt {
    const GOODS_NAME = 'goods_name';
    const GOODS_IMAGE = 'goods_image';
    const GOODS_INFO = 'goods_info';

    //字体大小
    protected $bodySize = 15;

    //背景图设置
    protected $backgroundHeight = 960;
    protected $backgroundWidth = 960;
    protected $backgroundOffsetX = 0;
    protected $backgroundOffsetY = 0;

    //标题设置
    protected $titleHeight = 50;
    protected $titleWidth = 600;
    protected $titleOffsetX = 350;
    protected $titleOffsetY = 50;

    //商品图片设置
    protected $goodImageHeight = 300;
    protected $goodImageWidth = 300;
    protected $goodImageOffsetX = 80;
    protected $goodImageOffsetY = 170;

    //商品内容设置
    protected $goodInfoHeight = 600;
    protected $goodInfoWidth = 600;
    protected $goodInfoOffsetX = 350;
    protected $goodInfoOffsetY = 170;

    public $background_image = '/ppt/background.jpg';//背景图片
    public $default_image = '/ppt/default.jpg';//默认图片

    public function exportPpt($data = []){
        $start_time = microtime(true);
        //1.创建ppt对象
        $objPHPPowerPoint = new PhpPresentation();

        //2.设置属性
        $objPHPPowerPoint->getDocumentProperties()->setCreator('PHPOffice')
            ->setLastModifiedBy('PHPPresentation Team')
            ->setTitle('Sample 02 Title')
            ->setSubject('Sample 02 Subject')
            ->setDescription('Sample 02 Description')
            ->setKeywords('office 2007 openxml libreoffice odt php')
            ->setCategory('Sample Category');

        //3.删除第一页(多页最好删除)
        $objPHPPowerPoint->removeSlideByIndex(0);

        //4.创建幻灯片
        //根据需求 调整for循环
        for ($i = 0; $i < count($data); $i++) {
            //创建幻灯片并添加到这个演示中
            $slide = $objPHPPowerPoint->createSlide();
            //创建一个背景(图)
            $shape = $slide->createDrawingShape();
            $shape->setName('背景图片')
                ->setDescription('背景图片 描述')
                ->setPath(public_path().$this->background_image)
                ->setHeight($this->backgroundHeight)
                ->setWidth($this->backgroundWidth)
                ->setOffsetX($this->backgroundOffsetX)
                ->setOffsetY($this->backgroundOffsetY);
            //$shape->setWidthAndHeight(850,600);
            /*$shape->getShadow()->setVisible(true)
                ->setDirection(45)
                ->setDistance(10);*/

            //创建一个标题(文本)
            $shape = $slide->createRichTextShape()
                ->setHeight($this->titleHeight)
                ->setWidth($this->titleWidth)
                ->setOffsetX($this->titleOffsetX)
                ->setOffsetY($this->titleOffsetY);
            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $textRun = $shape->createTextRun($data[$i][static::GOODS_NAME]);
            $textRun->getFont()->setBold(true)
                ->setSize($this->bodySize)
                ->setColor(new Color(Color::COLOR_BLACK));

            //创建一个商品图片(图)
            $shape = $slide->createDrawingShape();
            if($data[$i][static::GOODS_IMAGE]){
                $data[$i][static::GOODS_IMAGE] = FileMixin::makeDownloadUrl($data[$i][static::GOODS_IMAGE]);
                //存储图片到本地
                $image = static::keepPicture($data[$i][static::GOODS_IMAGE]);
                if($image){
                    $data[$i][static::GOODS_IMAGE] = $image;
                }else{
                    $data[$i][static::GOODS_IMAGE] = public_path().$this->default_image;
                }
                unset($image);
            }else{
                //默认图片
                $data[$i][static::GOODS_IMAGE] = public_path().$this->default_image;
            }
            $shape->setName('商品图片')
                ->setDescription('商品图片 描述')
                ->setPath(public_path().'/ppt/'.$data[$i][static::GOODS_IMAGE])
                ->setHeight($this->goodImageHeight)
                ->setWidth($this->goodImageWidth)
                ->setOffsetX($this->goodImageOffsetX)
                ->setOffsetY($this->goodImageOffsetY);

            // 创建一个商品内容(文本)
            $shape = $slide->createRichTextShape()
                ->setHeight($this->goodInfoHeight)
                ->setWidth($this->goodInfoWidth)
                ->setOffsetX($this->goodInfoOffsetX)
                ->setOffsetY($this->goodInfoOffsetY);
            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT );
            $textRun = $shape->createTextRun($data[$i][static::GOODS_INFO]);
            $textRun->getFont()->setBold(true)
                ->setSize($this->bodySize)
                ->setColor(new Color(Color::COLOR_BLACK));
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="商品.pptx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $oWriterPPTX = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');
        $oWriterPPTX->save('php://output');
        \Log::debug('PPT生成成功,用时:'.(microtime(true)-$start_time));
        exit;
    }

    public function savePpt($data = [],$save_file_name){
        $start_time = microtime(true);
        //1.创建ppt对象
        $objPHPPowerPoint = new PhpPresentation();

        //2.设置属性
        $objPHPPowerPoint->getDocumentProperties()->setCreator('PHPOffice')
            ->setLastModifiedBy('PHPPresentation Team')
            ->setTitle('Sample 02 Title')
            ->setSubject('Sample 02 Subject')
            ->setDescription('Sample 02 Description')
            ->setKeywords('office 2007 openxml libreoffice odt php')
            ->setCategory('Sample Category');

        //3.删除第一页(多页最好删除)
        $objPHPPowerPoint->removeSlideByIndex(0);

        //4.创建幻灯片
        //根据需求 调整for循环
        for ($i = 0; $i < count($data); $i++) {
            //创建幻灯片并添加到这个演示中
            $slide = $objPHPPowerPoint->createSlide();
            //创建一个背景(图)
            $shape = $slide->createDrawingShape();
            $shape->setName('背景图片')
                ->setDescription('背景图片 描述')
                ->setPath(public_path().$this->background_image)
                ->setHeight($this->backgroundHeight)
                ->setWidth($this->backgroundWidth)
                ->setOffsetX($this->backgroundOffsetX)
                ->setOffsetY($this->backgroundOffsetY);
            //$shape->setWidthAndHeight(850,600);
            /*$shape->getShadow()->setVisible(true)
                ->setDirection(45)
                ->setDistance(10);*/

            //创建一个标题(文本)
            $shape = $slide->createRichTextShape()
                ->setHeight($this->titleHeight)
                ->setWidth($this->titleWidth)
                ->setOffsetX($this->titleOffsetX)
                ->setOffsetY($this->titleOffsetY);
            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $textRun = $shape->createTextRun($data[$i][static::GOODS_NAME]);
            $textRun->getFont()->setBold(true)
                ->setSize($this->bodySize)
                ->setColor(new Color(Color::COLOR_BLACK));

            //创建一个商品图片(图)
            $shape = $slide->createDrawingShape();
            if($data[$i][static::GOODS_IMAGE]){
                $data[$i][static::GOODS_IMAGE] = FileMixin::makeDownloadUrl($data[$i][static::GOODS_IMAGE]);
                //存储图片到本地
                $image = static::keepPicture($data[$i][static::GOODS_IMAGE]);
                if($image){
                    $data[$i][static::GOODS_IMAGE] = $image;
                }else{
                    $data[$i][static::GOODS_IMAGE] = public_path().$this->default_image;
                }
                unset($image);
            }else{
                //默认图片
                $data[$i][static::GOODS_IMAGE] = public_path().$this->default_image;
            }
            $shape->setName('商品图片')
                ->setDescription('商品图片 描述')
                ->setPath(public_path().'/ppt/'.$data[$i][static::GOODS_IMAGE])
                ->setHeight($this->goodImageHeight)
                ->setWidth($this->goodImageWidth)
                ->setOffsetX($this->goodImageOffsetX)
                ->setOffsetY($this->goodImageOffsetY);

            // 创建一个商品内容(文本)
            $shape = $slide->createRichTextShape()
                ->setHeight($this->goodInfoHeight)
                ->setWidth($this->goodInfoWidth)
                ->setOffsetX($this->goodInfoOffsetX)
                ->setOffsetY($this->goodInfoOffsetY);
            $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT );
            $textRun = $shape->createTextRun($data[$i][static::GOODS_INFO]);
            $textRun->getFont()->setBold(true)
                ->setSize($this->bodySize)
                ->setColor(new Color(Color::COLOR_BLACK));
        }

        $oWriterPPTX = IOFactory::createWriter($objPHPPowerPoint, 'PowerPoint2007');

        $oWriterPPTX->save($save_file_name);
        \Log::debug('PPT生成成功,用时:'.(microtime(true)-$start_time));
        return true;
    }

    public static function keepPicture($img_url){
        try {
            $client = new \GuzzleHttp\Client();
            $ext = pathinfo($img_url,PATHINFO_EXTENSION);
            $data = $client->request('get',$img_url)->getBody()->getContents();
            $file_name = date('Y-m-d-H-i-s',time()).'x.'.$ext;
            $res = Storage::disk('ppt')->put($file_name, $data);
            if($res == true && !file_exists($file_name)){
                return $file_name;
            }else{
                return false;
            }
        } catch (\GuzzleHttp\RequestException $e) {
            \Log::error('获取远程图片失败');
            return $e->getMessage();
        }
    }
}


