<?php
/** 
 * 文件操作类
 * By net@1151.cn
 */
class File
{

    var $_fileName;
    var $_handler;
    var $_mode;

    function File($fileName)
    {
        $this->_fileName = $fileName;
    }

    /**
     * open
     * @param mode
     * @return handler
     */
    public function open( $mode = "r" )
    {
        $this->_handler = fopen( $this->_fileName, $mode );

        $this->_mode = $mode;

        return $this->_handler;
    }

    /**
     * Closes
     *
     * @return nothing
     */
    public  function close()
    {
        fclose( $this->_handler );
    }

    /**
     * 把一个文件读入数据
     * @return 文件数据
     */
    public function readFile()
    {
        $contents = Array();

        $contents = file( $this->_fileName );
        for( $i = 0; $i < count( $contents ); $i++ )
            $contents[$i] = trim( $contents[$i] );
        return $contents;
    }

    /**
     * 读文件
     * @param 读取大小 8192
     * @return 内容
     */
    public function read( $size = 4096 )
    {
        return( fread( $this->_handler, $size ));
    }

    /**
     * 是否文件未尾
     * @return ture or false
     */
    public function eof()
    {
        return feof( $this->_handler );
    }

    /**
     * fwrite
     * @param 写入的数据
     * @return true or file
     */
    public function write( $data )
    {
        return fwrite( $this->_handler, $data );
    }

    /**
     * 将文件截断到给定的长度
     * @param 长度
     * @return true or false
     */
    private function truncate( $length = 0 )
    {
        return ftruncate( $this->_handler, $length );
    }

    /**
     * 把数据或文字写入文件
     * @param 数据或文件
     * @return true or false
     */
    public function writeLines( $lines )
    {
        $this->truncate();

        foreach( $lines as $line ) {
            //print("read: \"".htmlentities($line)."\"<br/>");
            if( !$this->write( $line, strlen($line))) {
                return false;
            }
                 /*else
                 print("written: \"".htmlentities($line)."\"<br/>");*/
        }
        return true;
    }

    /**
     * 是否目录
     * @param 目录名
     * @return Returns true or false
     */
    static public function isDir( $file = null )
    {
        if( $file == null )
            $file = $this->_fileName;
        return is_dir( $file );
    }

    /**
     * 是否可写
     * @param 文件
     * @return true or false
     */
    static public function isWritable( $file = null )
    {
        if( $file == null )
            $file = $this->_fileName;
        return is_writable( $file );
    }

    /**
     * 文件是可读
     * @param 文件
     * @return true or false
     */
    static public function isReadable( $file = null )
    {
        if( $file == null )
            $file = $this->_fileName;

        clearstatcache();

        return is_readable( $file );
    }

    /**
     * 删除一个文件
     * @param 文件名
     * @return True or false
     */
    public function delete( $file = null )
    {
        if( $file == null )
            $file = $this->_fileName;

        if( !File::isReadable( $file ))
            return false;

        if( File::isDir( $file ))
            $result = rmdir( $file );
        else
            $result = unlink( $file );

        return $result;
    }

    /**
     * 删除一个目录
     *
     * @param 目录名称
     * @param 是否全部删除
     * @param 是否只删除文件留下目录
     * @return True or false
     */
    static public function deleteDir( $dirName, $recursive = false, $onlyFiles = false )
    {
        // 如果目录无读的权限
        if( !File::isReadable( $dirName ) || !File::exists( $dirName )) {
            return false;
        }

        if( !File::isDir( $dirName )) {
            return File::delete( $dirName );
        }

        $files = Glob( $dirName."/*" );
        foreach( $files as $file ) {
            if( File::isDir( $file )) {
                if( $recursive ) 
                    File::deleteDir( $file, $recursive, $onlyFiles );
            }

            if( File::isReadable( $file ))
                File::delete( $file );			}

            if( !$onlyFiles )
                File::delete( $dirName );

            return true;
    }

    /**
     * 新建目录
     * @param 目录路径
     * @param mode
     * @return Returns true or false
     */
    static public function createDir( $dirName, 
        $mode = '0644' )
    {
        if(File::exists($dirName)) return true;

        if(substr($dirName, strlen($dirName)-1) == "/" ){
            $dirName = substr($dirName, 0,strlen($dirName)-1);
        }

        $firstPart = substr($dirName,0,strrpos($dirName, "/" ));           

        if(file_exists($firstPart)){
            if(!mkdir($dirName,$mode)) return false;
            chmod( $dirName, $mode );
        } else {
            File::createDir($firstPart,$mode);
            if(!mkdir($dirName,$mode)) return false;
            chmod( $dirName, $mode );
        }

        return true;
    }


    /**
     * 取一个随机文件名
     * @return md5 string
     */
    static public function getTempName()
    {
        return md5(microtime());
    }

    /**
     * 取文件大小
     * @param file
     * @return 文件大小
     */
    public function getSize( $fileName = null )
    {
        if( $fileName == null )
            $fileName = $this->_fileName;

        $size = filesize( $fileName );
        if( !$size )
            return -1;
        else
            return $size;
    }

    /**
     * 重命名文件
     * @param 原文件名
     * @param 新文件名
     * @return Returns true or false
     */
    public function rename( $inFile, $outFile = null )
    {
        if( $outFile == null ) {
            $outFile = $inFile;
            $inFile  = $this->_fileName;
        }

        if (!copy($inFile, $outFile)) {
            // 复制失败，无法改名
            return FALSE;
        }

        unlink( $inFile );
        return TRUE;
    }

    /**
     * copy
     * @param inFile
     * @param destFile
     * @return True or false
     */
    static public function copy( $inFile, $outFile )
    {
        return @copy( $inFile, $outFile );
    }

    /**
     * chmod
     * @param file
     * @param mode
     * to 0644
     * @return true or false
     * @static
     */
    static public function chMod( $inFile, $mode = 0644 )
    {
        return chmod( $inFile, $mode );
    }

    /**
     * 文件是否存在
     * @param fileName
     * @return true or false
     */
    public  function exists( $fileName = null ) 
    {
        if( $fileName == null )
            $fileName = $this->_fileName;
        clearstatcache();	//清除文件状态缓存
        return file_exists( $fileName );
    } 

    /** 
     * touch一个文件
     * @param $filename 文件名
     * @return true or false
     */
    public function touch( $fileName = null )
    {
        if( $fileName == null )
            return false;
        return touch($fileName);
    }
}
?>