<?

// 联接FTP服务器
$conn = ftp_connect(ftp.server.com);

// 使用username和password登录
ftp_login($conn, “john”, “doe”);

// 获取远端系统类型
ftp_systype($conn);

// 列示文件
$filelist = ftp_nlist($conn, “.”);

// 下载文件
ftp_get($conn, “data.zip”, “data.zip”, FTP_BINARY);

// 关闭联接
ftp_quit($conn);

//初结化一个FTP联接，PHP提供了ftp_connect()这个函数，它使用主机名称和端口作为参数。在上面的例子里，主机名字为 “ftp.server.com”；如果端口没指定，PHP将会使用“21”作为缺省端口来建立联接。

//联接成功后ftp_connect()传回一个handle句柄；这个handle将被以后使用的FTP函数使用。
$conn = ftp_connect(ftp.server.com);

//一旦建立联接，使用ftp_login()发送一个用户名称和用户密码。你可以看到，这个函数ftp_login()使用了 ftp_connect()函数传来的handle，以确定用户名和密码能被提交到正确的服务器。
ftp_login($conn, “john”, “doe”);

// close connection
ftp_quit($conn);

//登录了FTP服务器，PHP提供了一些函数，它们能获取一些关于系统和文件以及目录的信息。
ftp_pwd()

//获取当前所在的目录
$here = ftp_pwd($conn);

//获取服务器端系统信息ftp_systype()
$server_os = ftp_systype($conn);

//被动模式（PASV）的开关，打开或关闭PASV（1表示开）
ftp_pasv($conn, 1);

//进入目录中用ftp_chdir()函数，它接受一个目录名作为参数。
ftp_chdir($conn, “public_html”);

//回到所在的目录父目录用ftp_cdup()实现
ftp_cdup($conn);

//建立或移动一个目录，这要使用ftp_mkdir()和ftp_rmdir()函数；注意：ftp_mkdir()建立成功的话，就会返回新建立的目录名。
ftp_mkdir($conn, “test”);

ftp_rmdir($conn, “test”);

//上传文件，ftp_put()函数能很好的胜任，它需要你指定一个本地文件名，上传后的文件名以及传输的类型。比方说：如果你想上传 “abc.txt”这个文件，上传后命名为“xyz.txt”，命令应该是这样：
ftp_put($conn, “xyz.txt”, “abc.txt”, FTP_ASCII);

//下载文件：PHP所提供的函数是ftp_get()，它也需要一个服务器上文件名，下载后的文件名，以及传输类型作为参数，例如：服务器端文件为his.zip，你想下载至本地机，并命名为hers.zip，命令如下：
ftp_get($conn, “hers.zip”, “his.zip”, FTP_BINARY);

//PHP提供两种方法：一种是简单列示文件名和目录，另一种就是详细的列示文件的大小，权限，创立时间等信息。

//第一种使用ftp_nlist()函数，第二种用ftp_rawlist().两种函数都需要一个目录名做为参数，都返回目录列做为一个数组，数组的每一个元素相当于列表的一行。
$filelist = ftp_nlist($conn, “.”);

//函数ftp_size()，它返回你所指定的文件的大小，使用BITES作为单位。要指出的是，如果它返回的是 “-1”的话，意味着这是一个目录
$filelist = ftp_size($conn, “data.zip”);

?>

<?php
/********************************************
* MODULE:FTP类
*******************************************/
class ftp
{
	public $off;                          // 返回操作状态(成功/失败)
	public $conn_id;                      // FTP连接

	/**
	* 方法：FTP连接
	* @FTP_HOST -- FTP主机
	* @FTP_PORT -- 端口
	* @FTP_USER -- 用户名
	* @FTP_PASS -- 密码
	*/
	function __construct($FTP_HOST,$FTP_PORT,$FTP_USER,$FTP_PASS)
	{
		$this->conn_id = @ftp_connect($FTP_HOST,$FTP_PORT) or die("FTP服务器连接失败");
		@ftp_login($this->conn_id,$FTP_USER,$FTP_PASS) or die("FTP服务器登陆失败");
		@ftp_pasv($this->conn_id,1); // 打开被动模拟
	}

	/**
	* 方法：上传文件
	* @path    -- 本地路径
	* @newpath -- 上传路径
	* @type    -- 若目标目录不存在则新建
	*/
	function up_file($path,$newpath,$type=true)
	{
		if($type) $this->dir_mkdirs($newpath);
		$this->off = @ftp_put($this->conn_id,$newpath,$path,FTP_BINARY);
		if(!$this->off) echo "文件上传失败,请检查权限及路径是否正确！";
	}

	/**
	* 方法：移动文件
	* @path    -- 原路径
	* @newpath -- 新路径
	* @type    -- 若目标目录不存在则新建
	*/
	function move_file($path,$newpath,$type=true)
	{
		if($type) $this->dir_mkdirs($newpath);
		$this->off = @ftp_rename($this->conn_id,$path,$newpath);
		if(!$this->off) echo "文件移动失败,请检查权限及原路径是否正确！";
	}

	/**
	* 方法：复制文件
	* 说明：由于FTP无复制命令,本方法变通操作为：下载后再上传到新的路径
	* @path    -- 原路径
	* @newpath -- 新路径
	* @type    -- 若目标目录不存在则新建
	*/
	function copy_file($path,$newpath,$type=true)
	{
		$downpath = "c:/tmp.dat";
		$this->off = @ftp_get($this->conn_id,$downpath,$path,FTP_BINARY);// 下载
		if(!$this->off) echo "文件复制失败,请检查权限及原路径是否正确！";
		$this->up_file($downpath,$newpath,$type);
	}

	/**
	* 方法：删除文件
	* @path -- 路径
	*/
	function del_file($path)
	{
		$this->off = @ftp_delete($this->conn_id,$path);
		if(!$this->off) echo "文件删除失败,请检查权限及路径是否正确！";
	}

	/**
	* 方法：生成目录
	* @path -- 路径
	*/
	function dir_mkdirs($path)
	{
		$path_arr  = explode('/',$path);              // 取目录数组
		$file_name = array_pop($path_arr);            // 弹出文件名
		$path_div  = count($path_arr);                // 取层数

		foreach($path_arr as $val)                    // 创建目录
		{
			if(@ftp_chdir($this->conn_id,$val) == FALSE)
			{
				$tmp = @ftp_mkdir($this->conn_id,$val);
				if($tmp == FALSE)
				{
					echo "目录创建失败,请检查权限及路径是否正确！";
					exit;
				}
				@ftp_chdir($this->conn_id,$val);
			}
		}
		
		for($i=1;$i<=$path_div;$i++)                  // 回退到根
		{
			@ftp_cdup($this->conn_id);
		}
	}

	/**
	* 方法：关闭FTP连接
	*/
	function close()
	{
		@ftp_close($this->conn_id);
	}
}
// class class_ftp end




/************************************** 测试 ***********************************
$ftp = new ftp('222.13.67.42',21,'hlj','123456');          // 打开FTP连接
$ftp->up_file('aa.wav','test/13548957217/bb.wav');         // 上传文件
//$ftp->move_file('aaa/aaa.php','aaa.php');                // 移动文件
//$ftp->copy_file('aaa.php','aaa/aaa.php');                // 复制文件
//$ftp->del_file('aaa.php');                               // 删除文件
$ftp->close();                                             // 关闭FTP连接
//******************************************************************************/
?>