<?php
/**
 * @name AlonePHP
 * @version 0.1
 * @author Tom <tom@awaysoft.com>
 * @date 2014-08-20
 * @description AlonePHP是一个单文件框架，适用于制作简单的小工具，支持网页及命令模式
 * @copyright Apache License, Version 2.0
 */
define('ALONE_ROOT', dirname(__FILE__));
define('IS_CLI', php_sapi_name() === 'cli');

/**
 * 默认控制器
 * Default Controller
 */
if (!function_exists('IndexController')) {
	function IndexController() {
		template('Index');
	}
}

/**
 * 默认控制器模板
 * Default Controller Template
 */
if (!function_exists('IndexTemplate')) {
	function IndexTemplate($args) {
		echo IS_CLI ? "欢迎使用AlonePHP框架\n" : '<meta charset="utf-8">欢迎使用AlonePHP框架';
	}
}

/**
 * 初始化函数，可以做权限验证，数据初始化等等
 * Initiazation Function, you can use it to check author or others
 */
if (!function_exists('init')) {
	function init() {

	}
}

/**
 * 框架运行函数
 * Framework run function
 */
function run() {

	/* 初始化 */
	init();
	/* 获取控制器 */
	if (IS_CLI) {
		/* 获取命令模式控制器 */
		$controller = param_get(1);
	} else {
		/* 获取网页模式 */
		$controller = get('c');
	}
	if (!$controller) {
		$controller = 'Index';
	}

	/* 移交控制权到相应的控制器 */
	controller($controller);
}

/**
 * @description 模板输出接口函数(Template function)
 * @param string $name 模板名称/Template Name
 * @param mixed  $args 传递到模板的参数，建议用关联数组/Template Args, better using relate array.
 */
function template($name, $args = '') {
	$templateName = $name . 'Template';
	if (function_exists($templateName)) {
		$templateName($args);
	} else {
		template('Error', "模板函数{$templateName}未找到！");
	}
}

/**
 * @description 控制器接口函数
 * @param string $name 控制器名称/Controller Name
 */
function controller($name) {
	$controllerName = $name . 'Controller';
	if (function_exists($controllerName)) {
		$controllerName();
	} else {
		template('Error', "控制器函数{$controllerName}未找到！");
	}
}

/**
 * @description 程序打开参数个数
 * @return integer 返回程序的参数个数 The Number of Params
 */
function param_count() {
	if (IS_CLI) {
		global $argc;
		return $argc - 1;
	} else {
		return 0;
	}
}

/**
 * @description 获取程序参数
 * @param integer $index, 参数的位置(The position of params)
 * @return string|boolean 返回第index个参数的值，The value of Params[$index];
 */
function param_get($index) {
	if (IS_CLI) {
		global $argc, $argv;
		if ($index >= $argc) {
			return false;
		} else {
			return $argv[$index];
		}
	} else {
		return false;
	}
}

/**  GET方法
 * @param string $name GET参数/The key of GET
 * @param string $filter 过滤函数/Filter function
 * @param string $default 默认值/Default Value
 * @return string
 */
function get($name, $filter = 'htmlspecialchars', $default = '') {
	if (!isset($_GET[$name])) {
		$result = $default;
	} else {
		$result = $_GET[$name];
	}
	return $filter($result);
}

/**  POST方法
 * @param string $name POST参数/The key of POST
 * @param string $filter 过滤函数/Filter function
 * @param string $default 默认值/Default Value
 * @return string
 */
function post($name, $filter = 'htmlspecialchars', $default = '') {
	if (!isset($_POST[$name])) {
		$result = $default;
	} else {
		$result = $_POST[$name];
	}
	return $filter($result);
}

/* 默认输出错误信息函数
Default Error Template
 */
function ErrorTemplate($args) {
	echo $args;
}

/* 运行框架
Run Framework
 */
run();

/* 下面为可选函数区，可根据实际需求删减 */
/* Options Functions, you can delete what you doesn't like */

/**
 * SESSION操作函数 / The Operator function with session
 * @param string $name key
 * @param mixed $value 写入session的值，如果读取session,此参数请留空,如果要删除这个session的内容，
 *              此参数请赋值为[delete],如果需要删除所有的session内容，此参数请赋值为[destroy]。
 *              if you want to read session, you can leave it empty, else the value will
 *              write to session,if you want to delete this key, you can leave it [delete],
 *              if you want to delete all keys, you can leave it [destroy]
 * @return mixed
 */
function session($name, $value = null) {
	static $is_init = false;
	static $cli_filename = '';
	static $session_obj = array();
	if (!$is_init) {
		/* CLI 模式下session存放在系统临时目录中，以文件方式存放 */
		if (IS_CLI) {
			$cli_filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . md5(__FILE__) . '.session';
			/* 如果文件存在，读取session */
			if (file_exists($cli_filename)) {
				$content = file_get_contents($cli_filename);
				$session_obj = unserialize($content);
				if (!is_array($session_obj)) {
					$session_obj = array();
				}
			}
		} else {
			session_start();
		}
		$is_init = true;
	}
	if ($value === null) {
		return IS_CLI ? $session_obj[$name] : $_SESSION[$name];
	} elseif ($value === '[destroy]') {
		IS_CLI ? ($session_obj = array()) : session_destroy();
	} elseif ($value === '[delete]') {
		if (IS_CLI) {
			unset($session_obj[$name]);
		} else {
			unset($_SESSION[$name]);
		}
	} else {
		IS_CLI ? ($session_obj[$name] = $value) : ($_SESSION[$name] = $value);
	}
	/* CLI模式下将session存入文件 */
	if (IS_CLI) {
		$content = serialize($session_obj);
		file_put_contents($cli_filename, $content);
	}
}

/**
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * Generate a random string, the length is six default
 * @param integer $len 长度/length
 * @param string $type 字串类型/char type 0:字母/letter,1:数字/number,2:大写字母/UpperLetter,3:小写字母/DownerLetter,4:中文/Chinese,其它/other:大小写数字混合/letter&number，但去除易混淆的字符/Donot have 0Oli etc.
 * @param string $add_chars 额外字符/addtional chars
 * @return string
 */
function rand_string($len = 6, $type = 5, $add_chars = '') {
	$str = '';
	switch ($type) {
		case 0:
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $add_chars;
			break;
		case 1:
			$chars = '0123456789' . $add_chars;
			break;
		case 2:
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $add_chars;
			break;
		case 3:
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $add_chars;
			break;
		case 4:
			$chars = '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借' . $add_chars;
			break;
		default:
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $add_chars;
			break;
	}
	/* 位数过长重复字符串一定次数 */
	if ($len > 10 && $type !== 4) {
		$chars = str_repeat($chars, $len);
	}

	for ($i = 0; $i < $len; $i++) {
		$str .= mb_substr($chars, mt_rand(0, mb_strlen($chars, 'utf-8') - 1), 1, 'utf-8');
	}
	return $str;
}

/**
 * 生成随机验证码 默认长度4位 字母和数字混合
 * Generate a rand verify code, the length is 4 by default
 * @param string $img 验证码图片类型/pic type gif, jpeg, png
 * @param integer $width 验证码长度/pic width
 * @param integer $height 验证码高度/pic height
 * @param integer $len 长度/string length
 * @param float $font_size 字体大小/Font size
 * @param float $angle 旋转角度/Font Angle
 * @param string $type 字串类型/string type 0:字母,1:数字,2:大写字母,3:小写字母,4:中文,其它:大小写数字混合，但去除易混淆的字符
 * @param string $add_chars 额外字符/addtional chars
 * @return string 验证码内容存在session中，key是verify_code/verify code is save in session, the session_key is verify_code
 */
function verify_code($img_type = 'jpeg', $width = 60, $height = 24, $len = 4, $font_size = 15, $angle = 0, $type = 5, $add_chars = '') {
	$code = rand_string($len, $type, $add_chars);
	session('verify_code', $code);
	header("Content-type: image/" . $img_type);

	if ($img_type != 'gif' && function_exists('imagecreatetruecolor')) {
		$im = imagecreatetruecolor($width, $height);
	} else {
		$im = imagecreate($width, $height);
	}

	$r = mt_rand(0, 255);
	$g = mt_rand(0, 255);
	$b = mt_rand(0, 255);
	/* 生成背景颜色 */
	$back_color = ImageColorAllocate($im, $r, $g, $b);
	/* 生成边框颜色 */
	$border_color = ImageColorAllocate($im, 0, 0, 0);
	/* 生成干扰点颜色 */
	$point_color = ImageColorAllocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));

	/* 背景位置 */
	imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $back_color);
	/* 边框位置 */
	imagerectangle($im, 0, 0, $width - 1, $height - 1, $border_color);

	/* 字符串颜色(背景反色) */
	$string_color = ImageColorAllocate($im, 255 - $r, 255 - $g, 255 - $b);

	/* 产生干扰点 */
	$point_number = mt_rand($len * 25, $len * 50);
	for ($i = 0; $i <= $point_number; $i++) {
		$pointX = mt_rand(2, $width - 2);
		$pointY = mt_rand(2, $height - 2);
		imagesetpixel($im, $pointX, $pointY, $point_color);
	}

	imagettftext($im, $font_size, 0, 4, 20, $string_color, ALONE_ROOT . '/Vera.ttf', $code);
	$image_out = 'Image' . $img_type;
	$image_out($im);
	@ImageDestroy($im);
}

/**
 * 用GET方式获取远程内容 / GET remote date
 * @param string $url 远程地址
 * @param array $header 特殊头,相关key请查询CURLOPT属性
 * @param int $timeout 超时时间
 * @return string|boolean 返回的内容,发生错误返回false
 */
function curl_get($url, $header = array(), $timeout = 5) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	foreach ($header as $key => $value) {
		curl_setopt($ch, $key, $value);
	}
	$file_contents = curl_redir_exec($ch);
	curl_close($ch);
	return $file_contents;
}

/**
 * 用POST方式获取远程内容 / POST remote date
 * @param string $url 远程地址
 * @param array $header 特殊头,相关key请查询CURLOPT属性
 * @param int $timeout 超时时间
 * @return string|boolean 返回的内容,发生错误返回false
 */
function curl_post($url, $post_data = array(), $header = array(), $timeout = 5) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	foreach ($header as $key => $value) {
		curl_setopt($ch, $key, $value);
	}
	$file_contents = curl_exec($ch);
	curl_close($ch);
	return $file_contents;
}

/* 获取header内容的长度 */
function curl_get_content_length($str) {
	$matches = array();
	preg_match('/Content-Length:(.*?)\n/', $str, $matches);
	$len = @trim(array_pop($matches));
	if (!$len) {
		$len = 0;
	}
	return (int) $len;
}

/* curl_get跳转用函数，以解决某些服务器CURLOPT_FOLLOWLOCATION不能用 */
function curl_redir_exec($ch) {
	static $curl_loops = 0;
	static $curl_max_loops = 20;// 最大循环次数

	if ($curl_loops++ >= $curl_max_loops) {
		$curl_loops = 0;
		return FALSE;
	}
	curl_setopt($ch, CURLOPT_HEADER, true);
	$data = curl_exec($ch);
/* 分离header和content */
	$content_len = curl_get_content_length($data);
	$header = substr($data, 0, strlen($data) - $content_len);
	$data = substr($data, strlen($header));

	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($http_code == 301 || $http_code == 302) {
		$matches = array();
		preg_match('/Location:(.*?)\n/', $header, $matches);
		$url = @parse_url(trim(array_pop($matches)));
		if (!$url) {
			$curl_loops = 0;
			return $data;
		}
		$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
		$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query'] ? '?' . $url['query'] : '');
		curl_setopt($ch, CURLOPT_URL, $new_url);
		return curl_redir_exec($ch);
	} else {
		$curl_loops = 0;
		return $data;
	}
}