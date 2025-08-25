<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/10 22:07
// +----------------------------------------------------------------------

namespace app\test\controller;

use addon\online_expo\app\job\CronStatJob;
use addon\online_expo\app\model\Record as RecordModel;
use addon\saler_tools\app\common\Utils;
use addon\saler_tools\app\job\LanguageTranslateJob;
use addon\saler_tools\app\job\QueueSendEmailJob;
use addon\saler_tools\app\model\Goods as GoodsModel;
use addon\saler_tools\app\model\Identify;
use addon\saler_tools\app\model\Order as OrderModel;
use addon\saler_tools\app\model\SalerToolsGoods;
use addon\saler_tools\app\model\SalerToolsGoodsBrand;
use addon\saler_tools\app\model\SalerToolsLanguagePackage;
use addon\saler_tools\app\model\Shop;
use addon\saler_tools\app\service\ExchangeRateService;
use addon\saler_tools\app\service\notify\EmailNotifyService;
use addon\saler_tools\app\service\translate\YoudaoService;
use app\service\core\schedule\CoreScheduleInstallService;
use think\facade\Cache;
use think\facade\Db;

/**
 *
 * Class Index
 * @package app\test\controller
 */
class Index
{

    public function __construct()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
    }

    public function index()
    {

    

    }


    /**
     * 站点初始化
     */
    private function devInit()
    {
        // 保护的站点
        $site_where = [
            ['site_id', '<>', 0]
        ];
        // 保护用户
        $user_where = [
            ['uid', '<>', 1]
        ];

        Db::name('sys_user_role')->where($site_where)->delete();
        Db::name('sys_user_log')->where($site_where)->delete();
        Db::name('sys_user')->where($user_where)->delete();

        Db::name('saler_tools_store')->where($site_where)->delete();
        Db::name('saler_tools_shop_apply')->delete(true);
        Db::name('saler_tools_shop')->delete(true);
        Db::name('saler_tools_share')->delete(true);
        Db::name('saler_tools_order_service')->delete(true);
        Db::name('saler_tools_order')->delete(true);
        Db::name('saler_tools_inventory_goods')->delete(true);
        Db::name('saler_tools_inventory')->delete(true);

        Db::name('saler_tools_identify_user')->delete(true);
        Db::name('saler_tools_identify_log')->delete(true);
        Db::name('saler_tools_identify')->delete(true);

        // 商品相关
        Db::name('saler_tools_goods_cost')->delete(true);
        Db::name('saler_tools_goods_attr')->delete(true);
        Db::name('saler_tools_goods')->delete(true);
        Db::name('saler_tools_dict')->delete(true);

        // 店铺联系人
        Db::name('saler_tools_contact')->delete(true);
        Db::name('saler_tools_user_oauth')->where($user_where)->delete(true);

        // 收藏
        Db::name('saler_tools_collect')->delete(true);

        //  验证码发送
        Db::name('saler_tools_captcha')->delete(true);

        // 账单
        Db::name('saler_tools_bill_record')->delete(true);
        Db::name('saler_tools_bill')->delete(true);

        // 店铺地址
        Db::name('saler_tools_address')->delete(true);

        // 线上展会
        Db::name('online_expo_goods_enquiry')->delete(true);
        Db::name('online_expo_goods_log')->delete(true);
        Db::name('online_expo_stat')->delete(true);


        Cache::clear();
    }

    public function testSendEmail()
    {
        (new EmailNotifyService())->sendTemplate(['template_key' => 'REGISTER', 'lang_key' => 'zh-Hans', 'email' => 'muspcn@gmail.com', 'data' => ['code' => '8888']]);
    }


    /**
     * 品牌数据生成
     */
    public function barndMake()
    {
        $str = <<<EOF
[
  {
    "classifyCode": "WB",
    "cnName": "爱彼",
    "enName": "Audemars Piguet",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240425/1714055763076.png",
    "id": 10796,
    "letter": "A",
    "pinyin": "AB",
    "showName": "爱彼/Audemars Piguet",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "艾美",
    "enName": "Maurice Lacroix",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714271378787.png",
    "id": 10863,
    "letter": "A",
    "pinyin": "AM",
    "showName": "艾美/Maurice Lacroix",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "艾米龙",
    "enName": "Emile Chouriet",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714271691250.png",
    "id": 10830,
    "letter": "A",
    "pinyin": "AML",
    "showName": "艾米龙/Emile Chouriet",
    "type": "0"
  },
  {
    "classifyCode": "WB,XX,FS,XB,QT,ZB,PS",
    "cnName": "阿玛尼",
    "enName": "Armani",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714271782475.png",
    "id": 10795,
    "letter": "A",
    "pinyin": "AMN",
    "showName": "阿玛尼/Armani",
    "type": "0"
  },
  {
    "classifyCode": "XB",
    "cnName": "爱马仕",
    "enName": "Hermes",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272284033.png",
    "id": 10847,
    "letter": "A",
    "pinyin": "AMS",
    "showName": "爱马仕/Hermes",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "朗格",
    "enName": "A.Lange & sohne",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274948947.png",
    "id": 10788,
    "letter": "A",
    "pinyin": "LG",
    "showName": "A.Lange & sohne/朗格",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,QT",
    "cnName": "麦昆",
    "enName": "Alexander McQueen",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284192664.png",
    "id": 20195,
    "letter": "A",
    "pinyin": "MK",
    "showName": "Alexander McQueen/麦昆",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,XX,PS,FS,QT",
    "cnName": "博柏利",
    "enName": "Burberry",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714271993992.png",
    "id": 10811,
    "letter": "B",
    "pinyin": "BBL",
    "showName": "博柏利/Burberry",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "百达翡丽",
    "enName": "Patek Philippe",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272060383.png",
    "id": 10879,
    "letter": "B",
    "pinyin": "BDFL",
    "showName": "百达翡丽/Patek Philippe",
    "type": "0"
  },
  {
    "classifyCode": "XB,ZB,XX,FS,QT",
    "cnName": "葆蝶家",
    "enName": "Bottega Veneta",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272158853.png",
    "id": 10806,
    "letter": "B",
    "pinyin": "BDJ",
    "showName": "葆蝶家/Bottega Veneta",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,ZB,PS",
    "cnName": "宝格丽",
    "enName": "Bvlgari",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272455830.png",
    "id": 10812,
    "letter": "B",
    "pinyin": "BGL",
    "showName": "宝格丽/Bvlgari",
    "type": "0"
  },
  {
    "classifyCode": "WB,ZB,QT",
    "cnName": "宝玑",
    "enName": "Breguet",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272861801.png",
    "id": 10808,
    "letter": "B",
    "pinyin": "BJ",
    "showName": "宝玑/Breguet",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "伯爵",
    "enName": "Piaget",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272409265.png",
    "id": 10881,
    "letter": "B",
    "pinyin": "BJ",
    "showName": "伯爵/Piaget",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX",
    "cnName": "巴利",
    "enName": "Bally",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272533014.png",
    "id": 10799,
    "letter": "B",
    "pinyin": "BL",
    "showName": "巴利/Bally",
    "type": "0"
  },
  {
    "classifyCode": "XB,PS,XX",
    "cnName": "巴黎世家",
    "enName": "Balenciaga",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273322783.png",
    "id": 10798,
    "letter": "B",
    "pinyin": "BLSJ",
    "showName": "巴黎世家/Balenciaga",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "百年灵",
    "enName": "Breitling",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240425/1714057236482.png",
    "id": 10809,
    "letter": "B",
    "pinyin": "BNL",
    "showName": "百年灵/Breitling",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "宝珀",
    "enName": "Blancpain",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273311192.png",
    "id": 10805,
    "letter": "B",
    "pinyin": "BP",
    "showName": "宝珀/Blancpain",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "宝齐莱",
    "enName": "Carl F.Bucherer",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272678784.png",
    "id": 10814,
    "letter": "B",
    "pinyin": "BQL",
    "showName": "宝齐莱/Carl F.Bucherer",
    "type": "0"
  },
  {
    "classifyCode": "PS,QT",
    "cnName": "宝诗龙",
    "enName": "Boucheron",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273137105.png",
    "id": 10807,
    "letter": "B",
    "pinyin": "BSL",
    "showName": "宝诗龙/Boucheron",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "名士",
    "enName": "Baume & Mercier",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284086227.png",
    "id": 10801,
    "letter": "B",
    "pinyin": "MS",
    "showName": "Baume & Mercier/名士",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "宝齐莱",
    "enName": "Carl F.Bucherer",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272678784.png",
    "id": 10814,
    "letter": "C",
    "pinyin": "BQL",
    "showName": "Carl F.Bucherer/宝齐莱",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,XX,PS,FS,QT",
    "cnName": "蔻驰",
    "enName": "Coach",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274959220.png",
    "id": 10823,
    "letter": "C",
    "pinyin": "KC",
    "showName": "Coach/蔻驰",
    "type": "0"
  },
  {
    "classifyCode": "ZB,WB,XB,QT,PS",
    "cnName": "卡地亚",
    "enName": "Cartier",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274810381.png",
    "id": 10815,
    "letter": "C",
    "pinyin": "KDY",
    "showName": "Cartier/卡地亚",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "昆仑",
    "enName": "Corum",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275521810.png",
    "id": 10824,
    "letter": "C",
    "pinyin": "KL",
    "showName": "Corum/昆仑",
    "type": "0"
  },
  {
    "classifyCode": "XB,QT,FS",
    "cnName": "蔻依",
    "enName": "Chloe",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275520202.png",
    "id": 21876,
    "letter": "C",
    "pinyin": "KY",
    "showName": "Chloe/蔻依",
    "type": "0"
  },
  {
    "classifyCode": "XB,ZB,XX,PS,FS",
    "cnName": "赛琳",
    "enName": "Celine",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284618652.png",
    "id": 10817,
    "letter": "C",
    "pinyin": "SL",
    "showName": "Celine/赛琳",
    "type": "0"
  },
  {
    "classifyCode": "ZB,WB",
    "cnName": "尚美",
    "enName": "Chaumet",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284159257.png",
    "id": 10819,
    "letter": "C",
    "pinyin": "SM",
    "showName": "Chaumet/尚美",
    "type": "0"
  },
  {
    "classifyCode": "WB,ZB,PS",
    "cnName": "萧邦",
    "enName": "Chopard",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284676835.png",
    "id": 10820,
    "letter": "C",
    "pinyin": "XB",
    "showName": "Chopard/萧邦",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,ZB,PS,FS,QT",
    "cnName": "香奈儿",
    "enName": "Chanel",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284419403.png",
    "id": 10818,
    "letter": "C",
    "pinyin": "XNE",
    "showName": "Chanel/香奈儿",
    "type": "0"
  },
  {
    "classifyCode": "XB,WB,PS,FS,XX",
    "cnName": "迪奥",
    "enName": "Dior",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272817226.png",
    "id": 10827,
    "letter": "D",
    "pinyin": "DA",
    "showName": "迪奥/Dior",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "帝舵",
    "enName": "Tudor",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273157882.png",
    "id": 10907,
    "letter": "D",
    "pinyin": "DD",
    "showName": "帝舵/Tudor",
    "type": "0"
  },
  {
    "classifyCode": "XB",
    "cnName": "德尔沃",
    "enName": "Delvaux",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273276677.png",
    "id": 21879,
    "letter": "D",
    "pinyin": "DEW",
    "showName": "德尔沃/Delvaux",
    "type": "0"
  },
  {
    "classifyCode": "WB,ZB,PS,QT",
    "cnName": "蒂芙尼",
    "enName": "Tiffany",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273515433.png",
    "id": 10904,
    "letter": "D",
    "pinyin": "DFN",
    "showName": "蒂芙尼/Tiffany",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "艾米龙",
    "enName": "Emile Chouriet",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714271691250.png",
    "id": 10830,
    "letter": "E",
    "pinyin": "AML",
    "showName": "Emile Chouriet/艾米龙",
    "type": "0"
  },
  {
    "classifyCode": "XB,PS,FS,QT",
    "cnName": "芬迪",
    "enName": "Fendi",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273460973.png",
    "id": 10835,
    "letter": "F",
    "pinyin": "FD",
    "showName": "芬迪/Fendi",
    "type": "0"
  },
  {
    "classifyCode": "ZB,PS,QT",
    "cnName": "斐登",
    "enName": "Fred",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273080475.png",
    "id": 10837,
    "letter": "F",
    "pinyin": "FD",
    "showName": "斐登/Fred",
    "type": "0"
  },
  {
    "classifyCode": "ZB,WB,QT",
    "cnName": "梵克雅宝",
    "enName": "Van Cleef & Arpels",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273235704.png",
    "id": 10910,
    "letter": "F",
    "pinyin": "FKYB",
    "showName": "梵克雅宝/Van Cleef & Arpels",
    "type": "0"
  },
  {
    "classifyCode": "FS,PS,XX",
    "cnName": "菲拉格慕",
    "enName": "Salvatore Ferragamo",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273562015.png",
    "id": 10893,
    "letter": "F",
    "pinyin": "FLGM",
    "showName": "菲拉格慕/Salvatore Ferragamo",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "法穆兰",
    "enName": "Franck Muller",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273408861.png",
    "id": 21872,
    "letter": "F",
    "pinyin": "FML",
    "showName": "法穆兰/Franck Muller",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,QT",
    "cnName": "范思哲",
    "enName": "Versace",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273483171.png",
    "id": 10911,
    "letter": "F",
    "pinyin": "FSZ",
    "showName": "范思哲/Versace",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,ZB,XX,PS,FS,QT",
    "cnName": "古驰",
    "enName": "Gucci",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274203439.png",
    "id": 10845,
    "letter": "G",
    "pinyin": "GC",
    "showName": "古驰/Gucci",
    "type": "0"
  },
  {
    "classifyCode": "WB,ZB",
    "cnName": "格拉夫",
    "enName": "Graff",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240606/1717665073392.png",
    "id": 32010,
    "letter": "G",
    "pinyin": "GLF",
    "showName": "格拉夫/Graff",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "格拉苏蒂",
    "enName": "Glashutte Original",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274291046.png",
    "id": 10843,
    "letter": "G",
    "pinyin": "GLSD",
    "showName": "格拉苏蒂/Glashutte Original",
    "type": "0"
  },
  {
    "classifyCode": "XB,QT",
    "cnName": "高雅德",
    "enName": "Goyard",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274066170.png",
    "id": 21877,
    "letter": "G",
    "pinyin": "GYD",
    "showName": "高雅德/Goyard",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,QT,ZB",
    "cnName": "纪梵希",
    "enName": "Givenchy",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273758706.png",
    "id": 10842,
    "letter": "G",
    "pinyin": "JFX",
    "showName": "Givenchy/纪梵希",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "芝柏",
    "enName": "Girard-Perregaux",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284816769.png",
    "id": 10841,
    "letter": "G",
    "pinyin": "ZB",
    "showName": "Girard-Perregaux/芝柏",
    "type": "0"
  },
  {
    "classifyCode": "WB,QT",
    "cnName": "豪利时",
    "enName": "Oris",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273931021.png",
    "id": 10875,
    "letter": "H",
    "pinyin": "HLS",
    "showName": "豪利时/Oris",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,FS,QT",
    "cnName": "华伦天奴",
    "enName": "Valentino",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273864881.png",
    "id": 21874,
    "letter": "H",
    "pinyin": "HLTN",
    "showName": "华伦天奴/Valentino",
    "type": "0"
  },
  {
    "classifyCode": "WB,QT",
    "cnName": "汉米尔顿",
    "enName": "Hamilton",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274499479.png",
    "id": 10846,
    "letter": "H",
    "pinyin": "HMED",
    "showName": "汉米尔顿/Hamilton",
    "type": "0"
  },
  {
    "classifyCode": "XB",
    "cnName": "爱马仕",
    "enName": "Hermes",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272284033.png",
    "id": 10847,
    "letter": "H",
    "pinyin": "AMS",
    "showName": "Hermes/爱马仕",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "宇舶",
    "enName": "Hublot",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284636750.png",
    "id": 10848,
    "letter": "H",
    "pinyin": "YB",
    "showName": "Hublot/宇舶",
    "type": "0"
  },
  {
    "classifyCode": "FS",
    "cnName": "三宅一生",
    "enName": "Issey Miyake",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284538305.png",
    "id": 21880,
    "letter": "I",
    "pinyin": "SZYS",
    "showName": "Issey Miyake/三宅一生",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "万国",
    "enName": "IWC",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284323777.png",
    "id": 10850,
    "letter": "I",
    "pinyin": "WG",
    "showName": "IWC/万国",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,QT,ZB",
    "cnName": "纪梵希",
    "enName": "Givenchy",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273758706.png",
    "id": 10842,
    "letter": "J",
    "pinyin": "JFX",
    "showName": "纪梵希/Givenchy",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "精工",
    "enName": "Seiko",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274480946.png",
    "id": 10895,
    "letter": "J",
    "pinyin": "JG",
    "showName": "精工/Seiko",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "积家",
    "enName": "Jaeger LeCoultre",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274057482.png",
    "id": 10851,
    "letter": "J",
    "pinyin": "JJ",
    "showName": "积家/Jaeger LeCoultre",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "江诗丹顿",
    "enName": "Vacheron Constantin",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274408376.png",
    "id": 10909,
    "letter": "J",
    "pinyin": "JSDD",
    "showName": "江诗丹顿/Vacheron Constantin",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "雅克德罗",
    "enName": "Jaquet Droz",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284512391.png",
    "id": 10852,
    "letter": "J",
    "pinyin": "YKDL",
    "showName": "Jaquet Droz/雅克德罗",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,XX,PS,FS,QT",
    "cnName": "蔻驰",
    "enName": "Coach",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274959220.png",
    "id": 10823,
    "letter": "K",
    "pinyin": "KC",
    "showName": "蔻驰/Coach",
    "type": "0"
  },
  {
    "classifyCode": "ZB,WB,XB,QT,PS",
    "cnName": "卡地亚",
    "enName": "Cartier",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274810381.png",
    "id": 10815,
    "letter": "K",
    "pinyin": "KDY",
    "showName": "卡地亚/Cartier",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "昆仑",
    "enName": "Corum",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275521810.png",
    "id": 10824,
    "letter": "K",
    "pinyin": "KL",
    "showName": "昆仑/Corum",
    "type": "0"
  },
  {
    "classifyCode": "XB,QT,FS",
    "cnName": "蔻依",
    "enName": "Chloe",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275520202.png",
    "id": 21876,
    "letter": "K",
    "pinyin": "KY",
    "showName": "蔻依/Chloe",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "理查米尔",
    "enName": "Richard Mille",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275163755.png",
    "id": 21873,
    "letter": "L",
    "pinyin": "LCME",
    "showName": "理查米尔/Richard Mille",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "雷达",
    "enName": "Rado",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275207633.png",
    "id": 10885,
    "letter": "L",
    "pinyin": "LD",
    "showName": "雷达/Rado",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "朗格",
    "enName": "A.Lange & sohne",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274948947.png",
    "id": 10788,
    "letter": "L",
    "pinyin": "LG",
    "showName": "朗格/A.Lange & sohne",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "罗杰杜彼",
    "enName": "Roger Dubuis",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275405317.png",
    "id": 20183,
    "letter": "L",
    "pinyin": "LJDB",
    "showName": "罗杰杜彼/Roger Dubuis",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "劳力士",
    "enName": "Rolex",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284805025.png",
    "id": 10890,
    "letter": "L",
    "pinyin": "LLS",
    "showName": "劳力士/Rolex",
    "type": "0"
  },
  {
    "classifyCode": "WB,PS",
    "cnName": "浪琴",
    "enName": "Longines",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284924510.png",
    "id": 10860,
    "letter": "L",
    "pinyin": "LQ",
    "showName": "浪琴/Longines",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,FS,QT,PS",
    "cnName": "罗意威",
    "enName": "Loewe",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284192429.png",
    "id": 10858,
    "letter": "L",
    "pinyin": "LYW",
    "showName": "罗意威/Loewe",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,QT,WB,FS",
    "cnName": "路易威登",
    "enName": "Louis Vuitton",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714283796481.png",
    "id": 10861,
    "letter": "L",
    "pinyin": "LYWD",
    "showName": "路易威登/Louis Vuitton",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,FS,QT,PS",
    "cnName": "MCM",
    "enName": "MCM",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714283747519.png",
    "id": 10866,
    "letter": "M",
    "pinyin": "MCM",
    "showName": "MCM",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "美度",
    "enName": "Mido",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284226929.png",
    "id": 10868,
    "letter": "M",
    "pinyin": "MD",
    "showName": "美度/Mido",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "摩凡陀",
    "enName": "Movado",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284389460.png",
    "id": 10871,
    "letter": "M",
    "pinyin": "MFT",
    "showName": "摩凡陀/Movado",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,QT",
    "cnName": "麦昆",
    "enName": "Alexander McQueen",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284192664.png",
    "id": 20195,
    "letter": "M",
    "pinyin": "MK",
    "showName": "麦昆/Alexander McQueen",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,PS,FS",
    "cnName": "迈克高仕",
    "enName": "Michael Kors",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284413806.png",
    "id": 10867,
    "letter": "M",
    "pinyin": "MKGS",
    "showName": "迈克高仕/Michael Kors",
    "type": "0"
  },
  {
    "classifyCode": "XB,ZB,XX,PS,FS,QT",
    "cnName": "缪缪",
    "enName": "Miu Miu",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284564858.png",
    "id": 10869,
    "letter": "M",
    "pinyin": "MM",
    "showName": "缪缪/Miu Miu",
    "type": "0"
  },
  {
    "classifyCode": "XB",
    "cnName": "摩奈",
    "enName": "MOYNAT",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284510307.png",
    "id": 21875,
    "letter": "M",
    "pinyin": "MN",
    "showName": "摩奈/MOYNAT",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "名士",
    "enName": "Baume & Mercier",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284086227.png",
    "id": 10801,
    "letter": "M",
    "pinyin": "MS",
    "showName": "名士/Baume & Mercier",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "艾美",
    "enName": "Maurice Lacroix",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714271378787.png",
    "id": 10863,
    "letter": "M",
    "pinyin": "AM",
    "showName": "Maurice Lacroix/艾美",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,QT",
    "cnName": "万宝龙",
    "enName": "Montblanc",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284474046.png",
    "id": 22074,
    "letter": "M",
    "pinyin": "WBL",
    "showName": "Montblanc/万宝龙",
    "type": "0"
  },
  {
    "classifyCode": "WB,ZB,PS",
    "cnName": "欧米茄",
    "enName": "Omega",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284641156.png",
    "id": 10874,
    "letter": "O",
    "pinyin": "OMQ",
    "showName": "欧米茄/Omega",
    "type": "0"
  },
  {
    "classifyCode": "WB,QT",
    "cnName": "豪利时",
    "enName": "Oris",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273931021.png",
    "id": 10875,
    "letter": "O",
    "pinyin": "HLS",
    "showName": "Oris/豪利时",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,ZB,QT",
    "cnName": "普拉达",
    "enName": "Prada",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284302174.png",
    "id": 10884,
    "letter": "P",
    "pinyin": "PLD",
    "showName": "普拉达/Prada",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "帕玛强尼",
    "enName": "Parmigiani",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284380318.png",
    "id": 10878,
    "letter": "P",
    "pinyin": "PMQN",
    "showName": "帕玛强尼/Parmigiani",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "沛纳海",
    "enName": "Panerai",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284278005.png",
    "id": 10877,
    "letter": "P",
    "pinyin": "PNH",
    "showName": "沛纳海/Panerai",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "百达翡丽",
    "enName": "Patek Philippe",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272060383.png",
    "id": 10879,
    "letter": "P",
    "pinyin": "BDFL",
    "showName": "Patek Philippe/百达翡丽",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "伯爵",
    "enName": "Piaget",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714272409265.png",
    "id": 10881,
    "letter": "P",
    "pinyin": "BJ",
    "showName": "Piaget/伯爵",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "理查米尔",
    "enName": "Richard Mille",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275163755.png",
    "id": 21873,
    "letter": "R",
    "pinyin": "LCME",
    "showName": "Richard Mille/理查米尔",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "雷达",
    "enName": "Rado",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275207633.png",
    "id": 10885,
    "letter": "R",
    "pinyin": "LD",
    "showName": "Rado/雷达",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "罗杰杜彼",
    "enName": "Roger Dubuis",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714275405317.png",
    "id": 20183,
    "letter": "R",
    "pinyin": "LJDB",
    "showName": "Roger Dubuis/罗杰杜彼",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "劳力士",
    "enName": "Rolex",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284805025.png",
    "id": 10890,
    "letter": "R",
    "pinyin": "LLS",
    "showName": "Rolex/劳力士",
    "type": "0"
  },
  {
    "classifyCode": "XB,ZB,XX,PS,FS",
    "cnName": "赛琳",
    "enName": "Celine",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284618652.png",
    "id": 10817,
    "letter": "S",
    "pinyin": "SL",
    "showName": "赛琳/Celine",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,ZB",
    "cnName": "圣罗兰",
    "enName": "Saint laurent",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284255580.png",
    "id": 10912,
    "letter": "S",
    "pinyin": "SLL",
    "showName": "圣罗兰/Saint laurent",
    "type": "0"
  },
  {
    "classifyCode": "ZB,WB",
    "cnName": "尚美",
    "enName": "Chaumet",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284159257.png",
    "id": 10819,
    "letter": "S",
    "pinyin": "SM",
    "showName": "尚美/Chaumet",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "斯沃琪",
    "enName": "Swatch",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284109058.png",
    "id": 10899,
    "letter": "S",
    "pinyin": "SWQ",
    "showName": "斯沃琪/Swatch",
    "type": "0"
  },
  {
    "classifyCode": "FS",
    "cnName": "三宅一生",
    "enName": "Issey Miyake",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284538305.png",
    "id": 21880,
    "letter": "S",
    "pinyin": "SZYS",
    "showName": "三宅一生/Issey Miyake",
    "type": "0"
  },
  {
    "classifyCode": "FS,PS,XX",
    "cnName": "菲拉格慕",
    "enName": "Salvatore Ferragamo",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273562015.png",
    "id": 10893,
    "letter": "S",
    "pinyin": "FLGM",
    "showName": "Salvatore Ferragamo/菲拉格慕",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "精工",
    "enName": "Seiko",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274480946.png",
    "id": 10895,
    "letter": "S",
    "pinyin": "JG",
    "showName": "Seiko/精工",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "泰格豪雅",
    "enName": "TAG Heuer",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284369939.png",
    "id": 10901,
    "letter": "T",
    "pinyin": "TGHY",
    "showName": "泰格豪雅/TAG Heuer",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,QT",
    "cnName": "汤丽柏琦",
    "enName": "Tory Burch",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284763379.png",
    "id": 22075,
    "letter": "T",
    "pinyin": "TLBQ",
    "showName": "汤丽柏琦/Tory Burch",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "天梭",
    "enName": "Tissot",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284759279.png",
    "id": 10905,
    "letter": "T",
    "pinyin": "TS",
    "showName": "天梭/Tissot",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "帝舵",
    "enName": "Tudor",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273157882.png",
    "id": 10907,
    "letter": "T",
    "pinyin": "DD",
    "showName": "Tudor/帝舵",
    "type": "0"
  },
  {
    "classifyCode": "WB,ZB,PS,QT",
    "cnName": "蒂芙尼",
    "enName": "Tiffany",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273515433.png",
    "id": 10904,
    "letter": "T",
    "pinyin": "DFN",
    "showName": "Tiffany/蒂芙尼",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "雅典",
    "enName": "Ulysse Nardin",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284678967.png",
    "id": 10908,
    "letter": "U",
    "pinyin": "YD",
    "showName": "Ulysse Nardin/雅典",
    "type": "0"
  },
  {
    "classifyCode": "ZB,WB,QT",
    "cnName": "梵克雅宝",
    "enName": "Van Cleef & Arpels",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273235704.png",
    "id": 10910,
    "letter": "V",
    "pinyin": "FKYB",
    "showName": "Van Cleef & Arpels/梵克雅宝",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,PS,FS,QT",
    "cnName": "范思哲",
    "enName": "Versace",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273483171.png",
    "id": 10911,
    "letter": "V",
    "pinyin": "FSZ",
    "showName": "Versace/范思哲",
    "type": "0"
  },
  {
    "classifyCode": "XB,XX,FS,QT",
    "cnName": "华伦天奴",
    "enName": "Valentino",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714273864881.png",
    "id": 21874,
    "letter": "V",
    "pinyin": "HLTN",
    "showName": "Valentino/华伦天奴",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "江诗丹顿",
    "enName": "Vacheron Constantin",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714274408376.png",
    "id": 10909,
    "letter": "V",
    "pinyin": "JSDD",
    "showName": "Vacheron Constantin/江诗丹顿",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,QT",
    "cnName": "万宝龙",
    "enName": "Montblanc",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284474046.png",
    "id": 22074,
    "letter": "W",
    "pinyin": "WBL",
    "showName": "万宝龙/Montblanc",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "万国",
    "enName": "IWC",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284323777.png",
    "id": 10850,
    "letter": "W",
    "pinyin": "WG",
    "showName": "万国/IWC",
    "type": "0"
  },
  {
    "classifyCode": "WB,ZB,PS",
    "cnName": "萧邦",
    "enName": "Chopard",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284676835.png",
    "id": 10820,
    "letter": "X",
    "pinyin": "XB",
    "showName": "萧邦/Chopard",
    "type": "0"
  },
  {
    "classifyCode": "WB,XB,ZB,PS,FS,QT",
    "cnName": "香奈儿",
    "enName": "Chanel",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284419403.png",
    "id": 10818,
    "letter": "X",
    "pinyin": "XNE",
    "showName": "香奈儿/Chanel",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "宇舶",
    "enName": "Hublot",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284636750.png",
    "id": 10848,
    "letter": "Y",
    "pinyin": "YB",
    "showName": "宇舶/Hublot",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "雅典",
    "enName": "Ulysse Nardin",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284678967.png",
    "id": 10908,
    "letter": "Y",
    "pinyin": "YD",
    "showName": "雅典/Ulysse Nardin",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "雅克德罗",
    "enName": "Jaquet Droz",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284512391.png",
    "id": 10852,
    "letter": "Y",
    "pinyin": "YKDL",
    "showName": "雅克德罗/Jaquet Droz",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "芝柏",
    "enName": "Girard-Perregaux",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284816769.png",
    "id": 10841,
    "letter": "Z",
    "pinyin": "ZB",
    "showName": "芝柏/Girard-Perregaux",
    "type": "0"
  },
  {
    "classifyCode": "WB",
    "cnName": "真力时",
    "enName": "Zenith",
    "iconUrl": "https://file.luxuryadmin.com/classifySub/20240428/1714284871863.png",
    "id": 10913,
    "letter": "Z",
    "pinyin": "ZLS",
    "showName": "真力时/Zenith",
    "type": "0"
  }
]
EOF;

        $json = json_decode($str, true);

        $file_dir = 'upload/saler_tools/';

        // 创建文件夹
        if (!is_dir($file_dir)) mkdir($file_dir, 0777, true);

        $inset = [];

        foreach ($json as $item) {

            $id = [];
            // 判断中文还是英文
            if (str_starts_with($item['showName'], $item['cnName'])) {
                $id = [
                    's_id'       => $item['id'],
                    'iconUrl'    => $item['iconUrl'],
                    'brand_name' => $item['cnName'],
                    'brand_en'   => $item['enName'],
                    'letter'     => $item['letter'],
                    'pinyin'     => $item['pinyin'],
                ];
            } else {
                $id = [
                    'brand_en'  => $item['enName'],
                    'letter_en' => $item['letter'],
                    'iconUrl'   => $item['iconUrl'],
                    's_id'      => $item['id'],
                ];
            }

            if (isset($inset[$id['s_id']])) {
                $inset[$id['s_id']] = array_merge($inset[$id['s_id']], $id);
            } else {
                $inset[$id['s_id']] = $id;
            }

        }

        // 遍历处理
        foreach ($inset as $k => $v) {
            // 下载图片
            $url  = $v['iconUrl'];
            $file = file_get_contents($url);
            // 将文件放入文件夹中
            $file_name = $file_dir . Utils::createno() . '.png';

            file_put_contents($file_name, $file);

            // 检测英文索引是否存在
            if (!isset($v['letter_en'])) $v['letter_en'] = mb_substr($v['brand_en'], 0, 1);
            $v['logo'] = $file_name;
            SalerToolsGoodsBrand::create($v);

        }


    }


}
