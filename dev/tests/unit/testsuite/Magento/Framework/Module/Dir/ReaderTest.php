<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Test class for \Magento\Framework\Module\Dir\File
 */
namespace Magento\Framework\Module\Dir;

use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_protFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dirsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_fileIteratorFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystemMock;

    protected function setUp()
    {
        $this->_protFactoryMock = $this->getMock(
            'Magento\Framework\App\Config\BaseFactory',
            [],
            [],
            '',
            false,
            false
        );
        $this->_dirsMock = $this->getMock('Magento\Framework\Module\Dir', [], [], '', false, false);
        $this->_baseConfigMock = $this->getMock(
            'Magento\Framework\App\Config\Base',
            [],
            [],
            '',
            false,
            false
        );
        $this->_moduleListMock = $this->getMock('Magento\Framework\Module\ModuleListInterface');
        $this->_filesystemMock = $this->getMock(
            '\Magento\Framework\Filesystem',
            [],
            [],
            '',
            false,
            false
        );
        $this->_fileIteratorFactory = $this->getMock(
            '\Magento\Framework\Config\FileIteratorFactory',
            [],
            [],
            '',
            false,
            false
        );

        $this->_model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            $this->_filesystemMock,
            $this->_fileIteratorFactory
        );
    }

    public function testGetModuleDirWhenCustomDirIsNotSet()
    {
        $this->_dirsMock->expects(
            $this->any()
        )->method(
            'getDir'
        )->with(
            'Test_Module',
            'etc'
        )->will(
            $this->returnValue('app/code/Test/Module/etc')
        );
        $this->assertEquals('app/code/Test/Module/etc', $this->_model->getModuleDir('etc', 'Test_Module'));
    }

    public function testGetModuleDirWhenCustomDirIsSet()
    {
        $moduleDir = 'app/code/Test/Module/etc/custom';
        $this->_dirsMock->expects($this->never())->method('getDir');
        $this->_model->setModuleDir('Test_Module', 'etc', $moduleDir);
        $this->assertEquals($moduleDir, $this->_model->getModuleDir('etc', 'Test_Module'));
    }

    public function testGetConfigurationFiles()
    {
        $configPath = 'app/code/Test/Module/etc/config.xml';
        $modulesDirectoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $modulesDirectoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $modulesDirectoryMock->expects($this->any())->method('isExist')
            ->with($configPath)
            ->will($this->returnValue(true));
        $this->_filesystemMock->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::MODULES)
            ->will($this->returnValue($modulesDirectoryMock));

        $this->_moduleListMock->expects($this->once())->method('getNames')->will($this->returnValue(['Test_Module']));
        $model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            $this->_filesystemMock,
            new FileIteratorFactory()
        );
        $model->setModuleDir('Test_Module', 'etc', 'app/code/Test/Module/etc');

        $this->assertEquals($configPath, $model->getConfigurationFiles('config.xml')->key());
    }

    public function testGetComposerJsonFiles()
    {
        $configPath = 'app/code/Test/Module/composer.json';
        $modulesDirectoryMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $modulesDirectoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $modulesDirectoryMock->expects($this->any())->method('isExist')
            ->with($configPath)
            ->will($this->returnValue(true));
        $this->_filesystemMock->expects($this->any())->method('getDirectoryRead')->with(DirectoryList::MODULES)
            ->will($this->returnValue($modulesDirectoryMock));

        $this->_moduleListMock->expects($this->once())->method('getNames')->will($this->returnValue(['Test_Module']));
        $model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            $this->_filesystemMock,
            new FileIteratorFactory()
        );
        $model->setModuleDir('Test_Module', '', 'app/code/Test/Module');

        $this->assertEquals($configPath, $model->getComposerJsonFiles()->key());
    }
}
