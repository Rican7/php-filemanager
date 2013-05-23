<?php

namespace OneMightyRoar\PhpFileManager\Tests;

use OneMightyRoar\PhpFileManager\FileObject;

/**
 * FileObjectTest
 *
 * @uses AbstractFileObjectTest
 * @package OneMightyRoar\PhpFileManager\Tests
 */
class FileObjectTest extends AbstractFileObjectTest
{

    public function testCreateFromBinary()
    {
        $test_file = $this->getTestFileByBaseName('photo.jpg');

        $binary_file_data = file_get_contents($test_file);
        $test_name = 'testtttttt';

        $file_object = FileObject::createFromBinary($binary_file_data);
        $file_object_with_name = FileObject::createFromBinary($binary_file_data, $test_name);

        $this->assertSame(FileObject::DEFAULT_NAME, $file_object->getName());
        $this->assertSame($test_name, $file_object_with_name->getName());

        $this->assertSame($binary_file_data, $file_object->getRaw(), $file_object_with_name->getRaw());

        return $file_object_with_name;
    }

    public function testCreateFromBase64Encoded()
    {
        $test_string = 'this is a test';
        $test_base64 = base64_encode($test_string);
        $test_name = 'testtttttt';

        $file_object = FileObject::createFromBase64Encoded($test_base64);
        $file_object_with_name = FileObject::createFromBase64Encoded($test_base64, $test_name);

        $this->assertSame(FileObject::DEFAULT_NAME, $file_object->getName());
        $this->assertSame($test_name, $file_object_with_name->getName());

        $this->assertSame($test_string, $file_object->getRaw(), $file_object_with_name->getRaw());

        return $file_object_with_name;
    }

    public function testCreateFromBase64EncodedWithImage()
    {
        $test_file = file_get_contents($this->getTestFileByBaseName('photo.base64'));
        $test_name = 'testtttttt';

        $file_object = FileObject::createFromBase64Encoded($test_file);
        $file_object_with_name = FileObject::createFromBase64Encoded($test_file, $test_name);

        $this->assertSame(FileObject::DEFAULT_NAME, $file_object->getName());
        $this->assertSame($test_name, $file_object_with_name->getName());

        $this->assertSame(base64_decode($test_file), $file_object->getRaw(), $file_object_with_name->getRaw());

        return $file_object_with_name;
    }

    public function testGetSetName()
    {
        $test_name = 'dog';

        $file_object = new FileObject(__FILE__);

        $this->assertNotSame($test_name, $file_object->getName());

        $file_object->setName($test_name);

        $this->assertNotNull($file_object->getName());
        $this->assertSame($test_name, $file_object->getName());
    }

    public function testGetSetMimeType()
    {
        $test_mime_type = 'text/html';

        $file_object = new FileObject(__FILE__);

        $this->assertNotSame($test_mime_type, $file_object->getMimeType());

        $file_object->setMimeType($test_mime_type);

        $this->assertNotNull($file_object->getMimeType());
        $this->assertSame($test_mime_type, $file_object->getMimeType());
    }

    public function testDetectMimeType()
    {
        $test_file = $this->getTestFileByBaseName('photo.jpg');
        $test_mime = 'image/jpeg';

        $file_object = new FileObject($test_file);

        $this->assertNotNull($file_object->detectMimeType());
        $this->assertSame($test_mime, $file_object->detectMimeType());
    }

    public function testDetectMimeTypeFromBuffer()
    {
        $test_file = file_get_contents($this->getTestFileByBaseName('photo.jpg'));
        $test_mime = 'image/jpeg';

        $this->assertNotNull(FileObject::detectMimeTypeFromBuffer($test_file));
        $this->assertSame($test_mime, FileObject::detectMimeTypeFromBuffer($test_file));
    }

    /**
     * @depends testCreateFromBase64Encoded
     */
    public function testDetectMimeTypeFromBase64($file_object)
    {
        $this->assertNotNull($file_object->detectMimeType());
        $this->assertSame('text/plain', $file_object->detectMimeType());
    }

    /**
     * @depends testCreateFromBase64EncodedWithImage
     */
    public function testDetectMimeTypeFromBase64WithImage($file_object)
    {
        $this->assertNotNull($file_object->detectMimeType());
        $this->assertSame('image/jpeg', $file_object->detectMimeType());
    }

    /**
     * @depends testCreateFromBase64Encoded
     */
    public function testGetResource($file_object)
    {
        $this->assertInternalType('resource', $file_object->getResource());
    }

    /**
     * @depends testCreateFromBinary
     */
    public function testGetWrapperInfo($file_object)
    {
        $test_protocol = 'data';
        $test_mime = 'image/jpeg';

        $wrapper_info = $file_object->getWrapperInfo();

        $this->assertGreaterThanOrEqual(3, $wrapper_info);
        $this->assertSame($test_protocol, $wrapper_info['protocol']);
        $this->assertSame($test_mime, $wrapper_info['MIME']);
    }

    /**
     * @depends testCreateFromBinary
     */
    public function testIsWrapped($file_object)
    {
        $this->assertTrue($file_object->isWrapped());
    }

    public function testIsWrappedBase64()
    {
        $file_object = new FileObject($this->getTestWrappedBase64());

        $this->assertTrue($file_object->isWrappedBase64());
    }

    /**
     * @depends testCreateFromBinary
     */
    public function testIsWrappedHex($file_object)
    {
        $this->assertTrue($file_object->isWrappedHex());
    }

    public function testGetRaw()
    {
        $test_file_jpg = $this->getTestFileByBaseName('photo.jpg');
        $test_file_base64 = $this->getTestFileByBaseName('photo.base64');
        $test_text = 'this is a test';
        $test_text_mime = 'text/donkey';

        $wrapped_binary = FileObject::createFromBinary(file_get_contents($test_file_jpg));
        $wrapped_base64_raw = FileObject::createFromBinary(base64_encode($test_text));
        $wrapped_base64_image = FileObject::createFromBinary(file_get_contents($test_file_base64));
        $raw_binary = new FileObject($test_file_jpg);
        $raw_base64 = new FileObject($test_file_base64);
        $raw_base64_text = new FileObject('data://'. $test_text_mime .';base64,'. base64_encode($test_text));
        $raw_text = new FileObject('data://'. $test_text_mime .','. $test_text);

        $this->assertSame(file_get_contents($test_file_jpg), $wrapped_binary->getRaw());
        $this->assertSame(file_get_contents($test_file_base64), $wrapped_base64_image->getRaw());
        $this->assertSame(base64_encode($test_text), $wrapped_base64_raw->getRaw());
        $this->assertSame(file_get_contents($test_file_jpg), $raw_binary->getRaw());
        $this->assertSame(file_get_contents($test_file_base64), $raw_base64->getRaw());
        $this->assertSame($test_text, $raw_base64_text->getRaw());
        $this->assertSame($test_text, $raw_text->getRaw());
    }

    public function testGetBase64()
    {
        $test_file_jpg = $this->getTestFileByBaseName('photo.jpg');
        $test_file_base64 = $this->getTestFileByBaseName('photo.base64');
        $test_file_base64_chunked = $this->getTestFileByBaseName('photo.chunked.base64');
        $test_text = 'this is a test';
        $test_text_mime = 'text/donkey';

        $wrapped_binary = FileObject::createFromBinary(file_get_contents($test_file_jpg));
        $wrapped_base64_image = FileObject::createFromBinary(file_get_contents($test_file_base64));
        $raw_binary = new FileObject($test_file_jpg);
        $raw_base64_text = new FileObject('data://'. $test_text_mime .';base64,'. base64_encode($test_text));
        $raw_text = new FileObject('data://'. $test_text_mime .','. $test_text);

        $this->assertSame(base64_encode(file_get_contents($test_file_jpg)), $wrapped_binary->getBase64(false));
        $this->assertSame(base64_encode(file_get_contents($test_file_base64)), $wrapped_base64_image->getBase64(false));
        $this->assertSame(base64_encode(file_get_contents($test_file_jpg)), $raw_binary->getBase64(false));
        $this->assertSame(base64_encode($test_text), $raw_base64_text->getBase64(false));
        $this->assertSame(base64_encode($test_text), $raw_text->getBase64(false));

        // Test chunked
        $this->assertSame(file_get_contents($test_file_base64_chunked), $raw_binary->getBase64());
        $this->assertSame(base64_decode(file_get_contents($test_file_base64_chunked)), $raw_binary->getRaw());
    }

    public function testGetHash()
    {
        $test_file_jpg = $this->getTestFileByBaseName('photo.jpg');
        $test_file_base64 = $this->getTestFileByBaseName('photo.base64');

        $wrapped_binary = FileObject::createFromBinary(file_get_contents($test_file_jpg));
        $wrapped_base64 = FileObject::createFromBinary(file_get_contents($test_file_base64));
        $raw_binary = new FileObject($test_file_jpg);
        $raw_base64 = new FileObject($test_file_base64);

        $this->assertSame($raw_binary->getHash(), $wrapped_binary->getHash());
        $this->assertSame($raw_base64->getHash(), $wrapped_base64->getHash());

        $this->assertNotSame($raw_binary->getHash(), $raw_base64->getHash());
        $this->assertNotSame($wrapped_binary->getHash(), $wrapped_base64->getHash());
    }

    public function testGetNameHash()
    {
        $test_file_jpg = $this->getTestFileByBaseName('photo.jpg');
        $test_file_base64 = $this->getTestFileByBaseName('photo.base64');

        $wrapped_binary = FileObject::createFromBinary(file_get_contents($test_file_jpg));
        $wrapped_base64 = FileObject::createFromBinary(file_get_contents($test_file_base64));
        $raw_binary = new FileObject($test_file_jpg);
        $raw_base64 = new FileObject($test_file_base64);

        $this->assertSame($wrapped_binary->getNameHash(), $wrapped_base64->getNameHash());

        $this->assertNotSame($raw_binary->getNameHash(), $raw_base64->getNameHash());
    }

    public function testGetExtension()
    {
        $test_file_jpg = $this->getTestFileByBaseName('photo.jpg');
        $test_file_base64 = $this->getTestFileByBaseName('photo.base64');
        $test_file_php = __FILE__;

        $wrapped_binary = FileObject::createFromBinary(file_get_contents($test_file_jpg));
        $wrapped_base64 = FileObject::createFromBinary(file_get_contents($test_file_base64));
        $wrapped_binary_php = FileObject::createFromBinary(file_get_contents($test_file_php));
        $raw_binary = new FileObject($test_file_jpg);
        $raw_base64 = new FileObject($test_file_base64);
        $raw_binary_php = new FileObject($test_file_php);

        $this->assertSame('jpeg', $wrapped_binary->getExtension());
        $this->assertSame('plain', $wrapped_base64->getExtension());
        $this->assertSame('c++', $wrapped_binary_php->getExtension());
        $this->assertSame('jpg', $raw_binary->getExtension());
        $this->assertSame('base64', $raw_base64->getExtension());
        $this->assertSame('php', $raw_binary_php->getExtension());

        // With dot?
        $this->assertNotContains('.', $wrapped_binary->getExtension(false));
        $this->assertContains('.', $wrapped_binary->getExtension(true));
    }

    public function testGetObfuscatedName()
    {
        $test_file_jpg = $this->getTestFileByBaseName('photo.jpg');
        $test_file_base64 = $this->getTestFileByBaseName('photo.base64');
        $test_file_php = __FILE__;

        $wrapped_binary = FileObject::createFromBinary(file_get_contents($test_file_jpg));
        $wrapped_base64 = FileObject::createFromBinary(file_get_contents($test_file_base64));
        $wrapped_binary_php = FileObject::createFromBinary(file_get_contents($test_file_php));
        $raw_binary = new FileObject($test_file_jpg);
        $raw_base64 = new FileObject($test_file_base64);
        $raw_binary_php = new FileObject($test_file_php);

        $this->assertSame($wrapped_binary->getObfuscatedName(false), $raw_binary->getObfuscatedName(false));
        $this->assertSame($wrapped_base64->getObfuscatedName(false), $raw_base64->getObfuscatedName(false));
        $this->assertSame($wrapped_binary_php->getObfuscatedName(false), $raw_binary_php->getObfuscatedName(false));

        $this->assertNotSame($wrapped_binary->getObfuscatedName(false), $wrapped_base64->getObfuscatedName(false));
        $this->assertNotSame($raw_binary->getObfuscatedName(false), $raw_base64->getObfuscatedName(false));

        // With extension?
        $this->assertNotContains('php', $raw_binary_php->getObfuscatedName(false));
        $this->assertContains('php', $raw_binary_php->getObfuscatedName(true));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testImageClassConverterFails()
    {
        $non_image_file_obj = new FileObject(__FILE__);
        $non_image_file_obj->getImageObject();
    }

    public function testImageClassConverterWorks()
    {
        $image_file_obj = new FileObject($this->getTestFileByBaseName('photo.jpg'));

        $this->assertNull($image_file_obj->getImageClass());
        $this->assertTrue($image_file_obj->getImageObject() instanceof FileObject);
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testCustomImageClassConverterFails()
    {
        $image_file_obj = new FileObject($this->getTestFileByBaseName('photo.jpg'));
        $image_file_obj->setImageClass('stdClass');
    }

    public function testCustomImageClassConverterWorks()
    {
        $test_class_name = 'OneMightyRoar\PhpFileManager\ImageObject';

        $image_file_obj = new FileObject($this->getTestFileByBaseName('photo.jpg'));
        $image_file_obj->setImageClass($test_class_name);

        $this->assertSame($test_class_name, $image_file_obj->getImageClass());
        $this->assertTrue($image_file_obj->getImageObject() instanceof FileObject);
        $this->assertTrue($image_file_obj->getImageObject() instanceof $test_class_name);
    }

    public function testMimeAliases()
    {
        $image_file_obj = new FileObject($this->getTestFileByBaseName('photo.jpg'));
        $text_file_obj = FileObject::createFromBinary('test and stuff');

        $this->assertTrue($image_file_obj->isImage());
        $this->assertFalse($text_file_obj->isImage());

        $this->assertTrue($text_file_obj->isText());
        $this->assertFalse($image_file_obj->isText());
    }
}
