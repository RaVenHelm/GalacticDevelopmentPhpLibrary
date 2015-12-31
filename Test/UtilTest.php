<?php 

    namespace GalacticDevelopment\Test;
        
    use GalacticDevelopment\Utility\Util;
    
    class UtilTest extends \PHPUnit_Framework_TestCase
    {
        public function testFileBuildPath()
        {
            $actual = Util::file_build_path('..' , 'tmp', 'src', 'hello.php');
            $expected = join(DIRECTORY_SEPARATOR, ['..' , 'tmp', 'src', 'hello.php']);
            $this->assertSame($expected, $actual); 
        }
        
    }
?>