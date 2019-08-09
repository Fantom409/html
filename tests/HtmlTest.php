<?php

namespace Yiisoft\Html\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Html\Html;

final class HtmlTest extends TestCase
{
    public function testEncode(): void
    {
        $this->assertSame('a&lt;&gt;&amp;&quot;&#039;�', Html::encode("a<>&\"'\x80"));
        $this->assertSame('Sam &amp; Dark', Html::encode('Sam & Dark'));
    }

    public function testDecode(): void
    {
        $this->assertSame("a<>&\"'", Html::decode('a&lt;&gt;&amp;&quot;&#039;'));
    }

    public function testTag(): void
    {
        $this->assertSame('<br>', Html::tag('br'));
        $this->assertSame('<span></span>', Html::tag('span'));
        $this->assertSame('<div>content</div>', Html::tag('div', 'content'));
        $this->assertSame('<input type="text" name="test" value="&lt;&gt;">', Html::tag('input', '', ['type' => 'text', 'name' => 'test', 'value' => '<>']));
        $this->assertSame('<span disabled></span>', Html::tag('span', '', ['disabled' => true]));
        $this->assertSame('test', Html::tag(false, 'test'));
        $this->assertSame('test', Html::tag(null, 'test'));
    }

    public function testBeginTag(): void
    {
        $this->assertSame('<br>', Html::beginTag('br'));
        $this->assertSame('<span id="test" class="title">', Html::beginTag('span', ['id' => 'test', 'class' => 'title']));
        $this->assertSame('', Html::beginTag(null));
        $this->assertSame('', Html::beginTag(false));
    }

    public function testEndTag(): void
    {
        $this->assertSame('</br>', Html::endTag('br'));
        $this->assertSame('</span>', Html::endTag('span'));
        $this->assertSame('', Html::endTag(null));
        $this->assertSame('', Html::endTag(false));
    }

    public function testStyle(): void
    {
        $content = 'a <>';
        $this->assertSame("<style>{$content}</style>", Html::style($content));
        $this->assertSame("<style type=\"text/less\">{$content}</style>", Html::style($content, ['type' => 'text/less']));
    }

    public function testScript(): void
    {
        $content = 'a <>';
        $this->assertSame("<script>{$content}</script>", Html::script($content));
        $this->assertSame("<script type=\"text/js\">{$content}</script>", Html::script($content, ['type' => 'text/js']));
    }

    public function testCssFile(): void
    {
        $this->assertSame('<link href="http://example.com" rel="stylesheet">', Html::cssFile('http://example.com'));
        $this->assertSame('<link href="/test" rel="stylesheet">', Html::cssFile(''));
        $this->assertSame("<!--[if IE 9]>\n" . '<link href="http://example.com" rel="stylesheet">' . "\n<![endif]-->", Html::cssFile('http://example.com', ['condition' => 'IE 9']));
        $this->assertSame("<!--[if (gte IE 9)|(!IE)]><!-->\n" . '<link href="http://example.com" rel="stylesheet">' . "\n<!--<![endif]-->", Html::cssFile('http://example.com', ['condition' => '(gte IE 9)|(!IE)']));
        $this->assertSame('<noscript><link href="http://example.com" rel="stylesheet"></noscript>', Html::cssFile('http://example.com', ['noscript' => true]));
    }

    public function testJsFile(): void
    {
        $this->assertSame('<script src="http://example.com"></script>', Html::jsFile('http://example.com'));
        $this->assertSame('<script src="/test"></script>', Html::jsFile(''));
        $this->assertSame("<!--[if IE 9]>\n" . '<script src="http://example.com"></script>' . "\n<![endif]-->", Html::jsFile('http://example.com', ['condition' => 'IE 9']));
        $this->assertSame("<!--[if (gte IE 9)|(!IE)]><!-->\n" . '<script src="http://example.com"></script>' . "\n<!--<![endif]-->", Html::jsFile('http://example.com', ['condition' => '(gte IE 9)|(!IE)']));
    }

    /**
     * @dataProvider dataProviderBeginFormSimulateViaPost
     *
     * @param string $expected
     * @param string $method
     */
    public function testBeginFormSimulateViaPost($expected, $method): void
    {
        $actual = Html::beginForm('/foo', $method);
        $this->assertStringMatchesFormat($expected, $actual);
    }

    /**
     * Data provider for {@see testBeginFormSimulateViaPost()}.
     * @return array test data
     */
    public function dataProviderBeginFormSimulateViaPost(): array
    {
        return [
            ['<form action="/foo" method="GET">', 'GET'],
            ['<form action="/foo" method="POST">', 'POST'],
            ['<form action="/foo" method="post">%A<input type="hidden" name="_method" value="DELETE">', 'DELETE'],
            ['<form action="/foo" method="post">%A<input type="hidden" name="_method" value="GETFOO">', 'GETFOO'],
            ['<form action="/foo" method="post">%A<input type="hidden" name="_method" value="POSTFOO">', 'POSTFOO'],
            ['<form action="/foo" method="post">%A<input type="hidden" name="_method" value="POSTFOOPOST">', 'POSTFOOPOST'],
        ];
    }

    public function testBeginForm(): void
    {
        $this->assertSame('<form action="/test" method="post">', Html::beginForm());
        $this->assertSame('<form action="/example" method="get">', Html::beginForm('/example', 'get'));
        $hiddens = [
            '<input type="hidden" name="id" value="1">',
            '<input type="hidden" name="title" value="&lt;">',
        ];
        $this->assertSame('<form action="/example" method="get">' . "\n" . implode("\n", $hiddens), Html::beginForm('/example?id=1&title=%3C', 'get'));

        $expected = '<form action="/foo" method="GET">%A<input type="hidden" name="p" value="">';
        $actual = Html::beginForm('/foo?p', 'GET');
        $this->assertStringMatchesFormat($expected, $actual);
    }

    public function testEndForm(): void
    {
        $this->assertSame('</form>', Html::endForm());
    }

    public function testA(): void
    {
        $this->assertSame('<a>something<></a>', Html::a('something<>'));
        $this->assertSame('<a href="/example">something</a>', Html::a('something', '/example'));
        $this->assertSame('<a href="/test">something</a>', Html::a('something', ''));
        $this->assertSame('<a href="http://www.быстроном.рф">http://www.быстроном.рф</a>', Html::a('http://www.быстроном.рф', 'http://www.быстроном.рф'));
        $this->assertSame('<a href="https://www.example.com/index.php?r=site%2Ftest">Test page</a>', Html::a('Test page', Url::to(['/site/test'], 'https')));
    }

    public function testMailto(): void
    {
        $this->assertSame('<a href="mailto:test&lt;&gt;">test<></a>', Html::mailto('test<>'));
        $this->assertSame('<a href="mailto:test&gt;">test<></a>', Html::mailto('test<>', 'test>'));
    }

    /**
     * @return array
     */
    public function imgDataProvider(): array
    {
        return [
            [
                '<img src="/example" alt="">',
                '/example',
                [],
            ],
            [
                '<img src="/test" alt="">',
                '',
                [],
            ],
            [
                '<img src="/example" width="10" alt="something">',
                '/example',
                [
                    'alt' => 'something',
                    'width' => 10,
                ],
            ],
            [
                '<img src="/base-url" srcset="" alt="">',
                '/base-url',
                [
                    'srcset' => [
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-9001w 9001w" alt="">',
                '/base-url',
                [
                    'srcset' => [
                        '9001w' => '/example-9001w',
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-100w 100w,/example-500w 500w,/example-1500w 1500w" alt="">',
                '/base-url',
                [
                    'srcset' => [
                        '100w' => '/example-100w',
                        '500w' => '/example-500w',
                        '1500w' => '/example-1500w',
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-1x 1x,/example-2x 2x,/example-3x 3x,/example-4x 4x,/example-5x 5x" alt="">',
                '/base-url',
                [
                    'srcset' => [
                        '1x' => '/example-1x',
                        '2x' => '/example-2x',
                        '3x' => '/example-3x',
                        '4x' => '/example-4x',
                        '5x' => '/example-5x',
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-1.42x 1.42x,/example-2.0x 2.0x,/example-3.99999x 3.99999x" alt="">',
                '/base-url',
                [
                    'srcset' => [
                        '1.42x' => '/example-1.42x',
                        '2.0x' => '/example-2.0x',
                        '3.99999x' => '/example-3.99999x',
                    ],
                ],
            ],
            [
                '<img src="/base-url" srcset="/example-1x 1x,/example-2x 2x,/example-3x 3x" alt="">',
                '/base-url',
                [
                    'srcset' => '/example-1x 1x,/example-2x 2x,/example-3x 3x',
                ],
            ],
        ];
    }

    /**
     * @dataProvider imgDataProvider
     * @param string $expected
     * @param string $src
     * @param array $options
     */
    public function testImg($expected, $src, $options): void
    {
        $this->assertSame($expected, Html::img($src, $options));
    }

    public function testLabel(): void
    {
        $this->assertSame('<label>something<></label>', Html::label('something<>'));
        $this->assertSame('<label for="a">something<></label>', Html::label('something<>', 'a'));
        $this->assertSame('<label class="test" for="a">something<></label>', Html::label('something<>', 'a', ['class' => 'test']));
    }

    public function testButton(): void
    {
        $this->assertSame('<button type="button">Button</button>', Html::button());
        $this->assertSame('<button type="button" name="test" value="value">content<></button>', Html::button('content<>', ['name' => 'test', 'value' => 'value']));
        $this->assertSame('<button type="submit" class="t" name="test" value="value">content<></button>', Html::button('content<>', ['type' => 'submit', 'name' => 'test', 'value' => 'value', 'class' => 't']));
    }

    public function testSubmitButton(): void
    {
        $this->assertSame('<button type="submit">Submit</button>', Html::submitButton());
        $this->assertSame('<button type="submit" class="t" name="test" value="value">content<></button>', Html::submitButton('content<>', ['name' => 'test', 'value' => 'value', 'class' => 't']));
    }

    public function testResetButton(): void
    {
        $this->assertSame('<button type="reset">Reset</button>', Html::resetButton());
        $this->assertSame('<button type="reset" class="t" name="test" value="value">content<></button>', Html::resetButton('content<>', ['name' => 'test', 'value' => 'value', 'class' => 't']));
    }

    public function testInput(): void
    {
        $this->assertSame('<input type="text">', Html::input('text'));
        $this->assertSame('<input type="text" class="t" name="test" value="value">', Html::input('text', 'test', 'value', ['class' => 't']));
    }

    public function testButtonInput(): void
    {
        $this->assertSame('<input type="button" value="Button">', Html::buttonInput());
        $this->assertSame('<input type="button" class="a" name="test" value="text">', Html::buttonInput('text', ['name' => 'test', 'class' => 'a']));
    }

    public function testSubmitInput(): void
    {
        $this->assertSame('<input type="submit" value="Submit">', Html::submitInput());
        $this->assertSame('<input type="submit" class="a" name="test" value="text">', Html::submitInput('text', ['name' => 'test', 'class' => 'a']));
    }

    public function testResetInput(): void
    {
        $this->assertSame('<input type="reset" value="Reset">', Html::resetInput());
        $this->assertSame('<input type="reset" class="a" name="test" value="text">', Html::resetInput('text', ['name' => 'test', 'class' => 'a']));
    }

    public function testTextInput(): void
    {
        $this->assertSame('<input type="text" name="test">', Html::textInput('test'));
        $this->assertSame('<input type="text" class="t" name="test" value="value">', Html::textInput('test', 'value', ['class' => 't']));
    }

    public function testHiddenInput(): void
    {
        $this->assertSame('<input type="hidden" name="test">', Html::hiddenInput('test'));
        $this->assertSame('<input type="hidden" class="t" name="test" value="value">', Html::hiddenInput('test', 'value', ['class' => 't']));
    }

    public function testPasswordInput(): void
    {
        $this->assertSame('<input type="password" name="test">', Html::passwordInput('test'));
        $this->assertSame('<input type="password" class="t" name="test" value="value">', Html::passwordInput('test', 'value', ['class' => 't']));
    }

    public function testFileInput(): void
    {
        $this->assertSame('<input type="file" name="test">', Html::fileInput('test'));
        $this->assertSame('<input type="file" class="t" name="test" value="value">', Html::fileInput('test', 'value', ['class' => 't']));
    }

    /**
     * @return array
     */
    public function textareaDataProvider(): array
    {
        return [
            [
                '<textarea name="test"></textarea>',
                'test',
                null,
                [],
            ],
            [
                '<textarea class="t" name="test">value&lt;&gt;</textarea>',
                'test',
                'value<>',
                ['class' => 't'],
            ],
            [
                '<textarea name="test">value&amp;lt;&amp;gt;</textarea>',
                'test',
                'value&lt;&gt;',
                [],
            ],
            [
                '<textarea name="test">value&lt;&gt;</textarea>',
                'test',
                'value&lt;&gt;',
                ['doubleEncode' => false],
            ],
        ];
    }

    /**
     * @dataProvider textareaDataProvider
     * @param string $expected
     * @param string $name
     * @param string $value
     * @param array $options
     */
    public function testTextarea($expected, $name, $value, $options): void
    {
        $this->assertSame($expected, Html::textarea($name, $value, $options));
    }

    public function testRadio(): void
    {
        $this->assertSame('<input type="radio" name="test" value="1">', Html::radio('test'));
        $this->assertSame('<input type="radio" class="a" name="test" checked>', Html::radio('test', true, ['class' => 'a', 'value' => null]));
        $this->assertSame('<input type="hidden" name="test" value="0"><input type="radio" class="a" name="test" value="2" checked>', Html::radio('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'value' => 2
        ]));
        $this->assertSame('<input type="hidden" name="test" value="0" disabled><input type="radio" name="test" value="2" disabled>', Html::radio('test', false, [
            'disabled' => true,
            'uncheck' => '0',
            'value' => 2
        ]));

        $this->assertSame('<label class="bbb"><input type="radio" class="a" name="test" checked> ccc</label>', Html::radio('test', true, [
            'class' => 'a',
            'value' => null,
            'label' => 'ccc',
            'labelOptions' => ['class' => 'bbb'],
        ]));
        $this->assertSame('<input type="hidden" name="test" value="0"><label><input type="radio" class="a" name="test" value="2" checked> ccc</label>', Html::radio('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'label' => 'ccc',
            'value' => 2,
        ]));
    }

    public function testCheckbox(): void
    {
        $this->assertSame('<input type="checkbox" name="test" value="1">', Html::checkbox('test'));
        $this->assertSame('<input type="checkbox" class="a" name="test" checked>', Html::checkbox('test', true, ['class' => 'a', 'value' => null]));
        $this->assertSame('<input type="hidden" name="test" value="0"><input type="checkbox" class="a" name="test" value="2" checked>', Html::checkbox('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'value' => 2
        ]));
        $this->assertSame('<input type="hidden" name="test" value="0" disabled><input type="checkbox" name="test" value="2" disabled>', Html::checkbox('test', false, [
            'disabled' => true,
            'uncheck' => '0',
            'value' => 2
        ]));

        $this->assertSame('<label class="bbb"><input type="checkbox" class="a" name="test" checked> ccc</label>', Html::checkbox('test', true, [
            'class' => 'a',
            'value' => null,
            'label' => 'ccc',
            'labelOptions' => ['class' => 'bbb'],
        ]));
        $this->assertSame('<input type="hidden" name="test" value="0"><label><input type="checkbox" class="a" name="test" value="2" checked> ccc</label>', Html::checkbox('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'label' => 'ccc',
            'value' => 2,
        ]));
        $this->assertSame('<input type="hidden" name="test" value="0" form="test-form"><label><input type="checkbox" class="a" name="test" value="2" form="test-form" checked> ccc</label>', Html::checkbox('test', true, [
            'class' => 'a',
            'uncheck' => '0',
            'label' => 'ccc',
            'value' => 2,
            'form' => 'test-form',
        ]));
    }

    public function testDropDownList(): void
    {
        $expected = <<<'EOD'
<select name="test">

</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::dropDownList('test'));
        $expected = <<<'EOD'
<select name="test">
<option value="value1">text1</option>
<option value="value2">text2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::dropDownList('test', null, $this->getDataItems()));
        $expected = <<<'EOD'
<select name="test">
<option value="value1">text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::dropDownList('test', 'value2', $this->getDataItems()));

        $expected = <<<'EOD'
<select name="test">
<option value="value1">text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::dropDownList('test', null, $this->getDataItems(), [
            'options' => [
                'value2' => ['selected' => true],
            ],
        ]));

        $expected = <<<'EOD'
<select name="test[]" multiple="true" size="4">

</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::dropDownList('test', null, [], ['multiple' => 'true']));

        $expected = <<<'EOD'
<select name="test[]" multiple="true" size="4">
<option value="0" selected>zero</option>
<option value="1">one</option>
<option value="value3">text3</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::dropDownList('test', [0], $this->getDataItems3(), ['multiple' => 'true']));
        $this->assertSameWithoutLE($expected, Html::dropDownList('test', new \ArrayObject([0]), $this->getDataItems3(), ['multiple' => 'true']));

        $expected = <<<'EOD'
<select name="test[]" multiple="true" size="4">
<option value="0">zero</option>
<option value="1" selected>one</option>
<option value="value3" selected>text3</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::dropDownList('test', ['1', 'value3'], $this->getDataItems3(), ['multiple' => 'true']));
        $this->assertSameWithoutLE($expected, Html::dropDownList('test', new \ArrayObject(['1', 'value3']), $this->getDataItems3(), ['multiple' => 'true']));
    }

    public function testListBox(): void
    {
        $expected = <<<'EOD'
<select name="test" size="4">

</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test'));
        $expected = <<<'EOD'
<select name="test" size="5">
<option value="value1">text1</option>
<option value="value2">text2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', null, $this->getDataItems(), ['size' => 5]));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1&lt;&gt;">text1&lt;&gt;</option>
<option value="value  2">text  2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2()));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1&lt;&gt;">text1&lt;&gt;</option>
<option value="value  2">text&nbsp;&nbsp;2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2(), ['encodeSpaces' => true]));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1&lt;&gt;">text1<></option>
<option value="value  2">text  2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2(), ['encode' => false]));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1&lt;&gt;">text1<></option>
<option value="value  2">text&nbsp;&nbsp;2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', null, $this->getDataItems2(), ['encodeSpaces' => true, 'encode' => false]));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1">text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', 'value2', $this->getDataItems()));
        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1" selected>text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', ['value1', 'value2'], $this->getDataItems()));

        $expected = <<<'EOD'
<select name="test[]" multiple size="4">

</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', null, [], ['multiple' => true]));
        $this->assertSameWithoutLE($expected, Html::listBox('test[]', null, [], ['multiple' => true]));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0"><select name="test" size="4">

</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', '', [], ['unselect' => '0']));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0" disabled><select name="test" disabled size="4">

</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', '', [], ['unselect' => '0', 'disabled' => true]));

        $expected = <<<'EOD'
<select name="test" size="4">
<option value="value1" selected>text1</option>
<option value="value2" selected>text2</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', new \ArrayObject(['value1', 'value2']), $this->getDataItems()));

        $expected = <<<'EOD'
<select name="test" size="4">
<option value="0" selected>zero</option>
<option value="1">one</option>
<option value="value3">text3</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', [0], $this->getDataItems3()));
        $this->assertSameWithoutLE($expected, Html::listBox('test', new \ArrayObject([0]), $this->getDataItems3()));

        $expected = <<<'EOD'
<select name="test" size="4">
<option value="0">zero</option>
<option value="1" selected>one</option>
<option value="value3" selected>text3</option>
</select>
EOD;
        $this->assertSameWithoutLE($expected, Html::listBox('test', ['1', 'value3'], $this->getDataItems3()));
        $this->assertSameWithoutLE($expected, Html::listBox('test', new \ArrayObject(['1', 'value3']), $this->getDataItems3()));
    }

    public function testCheckboxList(): void
    {
        $this->assertSame('<div></div>', Html::checkboxList('test'));

        $expected = <<<'EOD'
<div><label><input type="checkbox" name="test[]" value="value1"> text1</label>
<label><input type="checkbox" name="test[]" value="value2" checked> text2</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems()));
        $this->assertSameWithoutLE($expected, Html::checkboxList('test[]', ['value2'], $this->getDataItems()));

        $expected = <<<'EOD'
<div><label><input type="checkbox" name="test[]" value="value1&lt;&gt;"> text1&lt;&gt;</label>
<label><input type="checkbox" name="test[]" value="value  2"> text  2</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems2()));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0"><div><label><input type="checkbox" name="test[]" value="value1"> text1</label><br>
<label><input type="checkbox" name="test[]" value="value2" checked> text2</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems(), [
            'separator' => "<br>\n",
            'unselect' => '0',
        ]));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0" disabled><div><label><input type="checkbox" name="test[]" value="value1"> text1</label><br>
<label><input type="checkbox" name="test[]" value="value2"> text2</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', null, $this->getDataItems(), [
            'separator' => "<br>\n",
            'unselect' => '0',
            'disabled' => true,
        ]));

        $expected = <<<'EOD'
<div>0<label>text1 <input type="checkbox" name="test[]" value="value1"></label>
1<label>text2 <input type="checkbox" name="test[]" value="value2" checked></label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems(), [
            'item' => static function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::checkbox($name, $checked, ['value' => $value]));
            },
        ]));

        $expected = <<<'EOD'
0<label>text1 <input type="checkbox" name="test[]" value="value1"></label>
1<label>text2 <input type="checkbox" name="test[]" value="value2" checked></label>
EOD;
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', ['value2'], $this->getDataItems(), [
            'item' => static function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::checkbox($name, $checked, ['value' => $value]));
            },
            'tag' => false,
        ]));


        $this->assertSameWithoutLE($expected, Html::checkboxList('test', new \ArrayObject(['value2']), $this->getDataItems(), [
            'item' => static function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::checkbox($name, $checked, ['value' => $value]));
            },
            'tag' => false,
        ]));

        $expected = <<<'EOD'
<div><label><input type="checkbox" name="test[]" value="0" checked> zero</label>
<label><input type="checkbox" name="test[]" value="1"> one</label>
<label><input type="checkbox" name="test[]" value="value3"> text3</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', [0], $this->getDataItems3()));
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', new \ArrayObject([0]), $this->getDataItems3()));

        $expected = <<<'EOD'
<div><label><input type="checkbox" name="test[]" value="0"> zero</label>
<label><input type="checkbox" name="test[]" value="1" checked> one</label>
<label><input type="checkbox" name="test[]" value="value3" checked> text3</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', ['1', 'value3'], $this->getDataItems3()));
        $this->assertSameWithoutLE($expected, Html::checkboxList('test', new \ArrayObject(['1', 'value3']), $this->getDataItems3()));
    }

    public function testRadioList(): void
    {
        $this->assertSame('<div></div>', Html::radioList('test'));

        $expected = <<<'EOD'
<div><label><input type="radio" name="test" value="value1"> text1</label>
<label><input type="radio" name="test" value="value2" checked> text2</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems()));

        $expected = <<<'EOD'
<div><label><input type="radio" name="test" value="value1&lt;&gt;"> text1&lt;&gt;</label>
<label><input type="radio" name="test" value="value  2"> text  2</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems2()));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0"><div><label><input type="radio" name="test" value="value1"> text1</label><br>
<label><input type="radio" name="test" value="value2" checked> text2</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems(), [
            'separator' => "<br>\n",
            'unselect' => '0',
        ]));

        $expected = <<<'EOD'
<input type="hidden" name="test" value="0" disabled><div><label><input type="radio" name="test" value="value1"> text1</label><br>
<label><input type="radio" name="test" value="value2"> text2</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::radioList('test', null, $this->getDataItems(), [
            'separator' => "<br>\n",
            'unselect' => '0',
            'disabled' => true,
        ]));

        $expected = <<<'EOD'
<div>0<label>text1 <input type="radio" name="test" value="value1"></label>
1<label>text2 <input type="radio" name="test" value="value2" checked></label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems(), [
            'item' => static function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::radio($name, $checked, ['value' => $value]));
            },
        ]));

        $expected = <<<'EOD'
0<label>text1 <input type="radio" name="test" value="value1"></label>
1<label>text2 <input type="radio" name="test" value="value2" checked></label>
EOD;
        $this->assertSameWithoutLE($expected, Html::radioList('test', ['value2'], $this->getDataItems(), [
            'item' => static function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::radio($name, $checked, ['value' => $value]));
            },
            'tag' => false,
        ]));

        $this->assertSameWithoutLE($expected, Html::radioList('test', new \ArrayObject(['value2']), $this->getDataItems(), [
            'item' => static function ($index, $label, $name, $checked, $value) {
                return $index . Html::label($label . ' ' . Html::radio($name, $checked, ['value' => $value]));
            },
            'tag' => false,
        ]));

        $expected = <<<'EOD'
<div><label><input type="radio" name="test" value="0" checked> zero</label>
<label><input type="radio" name="test" value="1"> one</label>
<label><input type="radio" name="test" value="value3"> text3</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::radioList('test', [0], $this->getDataItems3()));
        $this->assertSameWithoutLE($expected, Html::radioList('test', new \ArrayObject([0]), $this->getDataItems3()));

        $expected = <<<'EOD'
<div><label><input type="radio" name="test" value="0"> zero</label>
<label><input type="radio" name="test" value="1"> one</label>
<label><input type="radio" name="test" value="value3" checked> text3</label></div>
EOD;
        $this->assertSameWithoutLE($expected, Html::radioList('test', ['value3'], $this->getDataItems3()));
        $this->assertSameWithoutLE($expected, Html::radioList('test', new \ArrayObject(['value3']), $this->getDataItems3()));
    }

    public function testUl(): void
    {
        $data = [
            1, 'abc', '<>',
        ];
        $expected = <<<'EOD'
<ul>
<li>1</li>
<li>abc</li>
<li>&lt;&gt;</li>
</ul>
EOD;
        $this->assertSameWithoutLE($expected, Html::ul($data));
        $expected = <<<'EOD'
<ul class="test">
<li class="item-0">1</li>
<li class="item-1">abc</li>
<li class="item-2"><></li>
</ul>
EOD;
        $this->assertSameWithoutLE($expected, Html::ul($data, [
            'class' => 'test',
            'item' => static function ($item, $index) {
                return "<li class=\"item-$index\">$item</li>";
            },
        ]));

        $this->assertSame('<ul class="test"></ul>', Html::ul([], ['class' => 'test']));

        $this->assertStringMatchesFormat('<foo>%A</foo>', Html::ul([], ['tag' => 'foo']));
    }

    public function testOl(): void
    {
        $data = [
            1, 'abc', '<>',
        ];
        $expected = <<<'EOD'
<ol>
<li class="ti">1</li>
<li class="ti">abc</li>
<li class="ti">&lt;&gt;</li>
</ol>
EOD;
        $this->assertSameWithoutLE($expected, Html::ol($data, [
            'itemOptions' => ['class' => 'ti'],
        ]));
        $expected = <<<'EOD'
<ol class="test">
<li class="item-0">1</li>
<li class="item-1">abc</li>
<li class="item-2"><></li>
</ol>
EOD;
        $this->assertSameWithoutLE($expected, Html::ol($data, [
            'class' => 'test',
            'item' => static function ($item, $index) {
                return "<li class=\"item-$index\">$item</li>";
            },
        ]));

        $this->assertSame('<ol class="test"></ol>', Html::ol([], ['class' => 'test']));
    }

    public function testRenderOptions(): void
    {
        $data = [
            'value1' => 'label1',
            'group1' => [
                'value11' => 'label11',
                'group11' => [
                    'value111' => 'label111',
                ],
                'group12' => [],
            ],
            'value2' => 'label2',
            'group2' => [],
        ];
        $expected = <<<'EOD'
<option value="">please&nbsp;select&lt;&gt;</option>
<option value="value1" selected>label1</option>
<optgroup label="group1">
<option value="value11">label11</option>
<optgroup label="group11">
<option class="option" value="value111" selected>label111</option>
</optgroup>
<optgroup class="group" label="group12">

</optgroup>
</optgroup>
<option value="value2">label2</option>
<optgroup label="group2">

</optgroup>
EOD;
        $attributes = [
            'prompt' => 'please select<>',
            'options' => [
                'value111' => ['class' => 'option'],
            ],
            'groups' => [
                'group12' => ['class' => 'group'],
            ],
            'encodeSpaces' => true,
        ];
        $this->assertSameWithoutLE($expected, Html::renderSelectOptions(['value111', 'value1'], $data, $attributes));

        $attributes = [
            'prompt' => 'please select<>',
            'options' => [
                'value111' => ['class' => 'option'],
            ],
            'groups' => [
                'group12' => ['class' => 'group'],
            ],
        ];
        $this->assertSameWithoutLE(str_replace('&nbsp;', ' ', $expected), Html::renderSelectOptions(['value111', 'value1'], $data, $attributes));

        // Attributes for prompt (https://github.com/yiisoft/yii2/issues/7420)

        $data = [
            'value1' => 'label1',
            'value2' => 'label2',
        ];
        $expected = <<<'EOD'
<option class="prompt" value="-1" label="None">Please select</option>
<option value="value1" selected>label1</option>
<option value="value2">label2</option>
EOD;
        $attributes = [
            'prompt' => [
                'text' => 'Please select', 'options' => ['class' => 'prompt', 'value' => '-1', 'label' => 'None'],
            ],
        ];
        $this->assertSameWithoutLE($expected, Html::renderSelectOptions(['value1'], $data, $attributes));
    }

    public function testRenderAttributes(): void
    {
        $this->assertSame('', Html::renderTagAttributes([]));
        $this->assertSame(' name="test" value="1&lt;&gt;"', Html::renderTagAttributes(['name' => 'test', 'empty' => null, 'value' => '1<>']));
        $this->assertSame(' checked disabled', Html::renderTagAttributes(['checked' => true, 'disabled' => true, 'hidden' => false]));
        $this->assertSame(' class="first second"', Html::renderTagAttributes(['class' => ['first', 'second']]));
        $this->assertSame('', Html::renderTagAttributes(['class' => []]));
        $this->assertSame(' style="width: 100px; height: 200px;"', Html::renderTagAttributes(['style' => ['width' => '100px', 'height' => '200px']]));
        $this->assertSame('', Html::renderTagAttributes(['style' => []]));

        $attributes = [
            'data' => [
                'foo' => [],
            ],
        ];
        $this->assertSame(' data-foo=\'[]\'', Html::renderTagAttributes($attributes));
    }

    public function testAddCssClass(): void
    {
        $options = [];
        Html::addCssClass($options, 'test');
        $this->assertSame(['class' => 'test'], $options);
        Html::addCssClass($options, 'test');
        $this->assertSame(['class' => 'test'], $options);
        Html::addCssClass($options, 'test2');
        $this->assertSame(['class' => 'test test2'], $options);
        Html::addCssClass($options, 'test');
        $this->assertSame(['class' => 'test test2'], $options);
        Html::addCssClass($options, 'test2');
        $this->assertSame(['class' => 'test test2'], $options);
        Html::addCssClass($options, 'test3');
        $this->assertSame(['class' => 'test test2 test3'], $options);
        Html::addCssClass($options, 'test2');
        $this->assertSame(['class' => 'test test2 test3'], $options);

        $options = [
            'class' => ['test'],
        ];
        Html::addCssClass($options, 'test2');
        $this->assertSame(['class' => ['test', 'test2']], $options);
        Html::addCssClass($options, 'test2');
        $this->assertSame(['class' => ['test', 'test2']], $options);
        Html::addCssClass($options, ['test3']);
        $this->assertSame(['class' => ['test', 'test2', 'test3']], $options);

        $options = [
            'class' => 'test',
        ];
        Html::addCssClass($options, ['test1', 'test2']);
        $this->assertSame(['class' => 'test test1 test2'], $options);
    }

    /**
     * @depends testAddCssClass
     */
    public function testMergeCssClass(): void
    {
        $options = [
            'class' => [
                'persistent' => 'test1',
            ],
        ];
        Html::addCssClass($options, ['persistent' => 'test2']);
        $this->assertSame(['persistent' => 'test1'], $options['class']);
        Html::addCssClass($options, ['additional' => 'test2']);
        $this->assertSame(['persistent' => 'test1', 'additional' => 'test2'], $options['class']);
    }

    public function testRemoveCssClass(): void
    {
        $options = ['class' => 'test test2 test3'];
        Html::removeCssClass($options, 'test2');
        $this->assertSame(['class' => 'test test3'], $options);
        Html::removeCssClass($options, 'test2');
        $this->assertSame(['class' => 'test test3'], $options);
        Html::removeCssClass($options, 'test');
        $this->assertSame(['class' => 'test3'], $options);
        Html::removeCssClass($options, 'test3');
        $this->assertSame([], $options);

        $options = ['class' => ['test', 'test2', 'test3']];
        Html::removeCssClass($options, 'test2');
        $this->assertSame(['class' => ['test', 2 => 'test3']], $options);
        Html::removeCssClass($options, 'test');
        Html::removeCssClass($options, 'test3');
        $this->assertSame([], $options);

        $options = [
            'class' => 'test test1 test2',
        ];
        Html::removeCssClass($options, ['test1', 'test2']);
        $this->assertSame(['class' => 'test'], $options);
    }

    public function testCssStyleFromArray(): void
    {
        $this->assertSame('width: 100px; height: 200px;', Html::cssStyleFromArray([
            'width' => '100px',
            'height' => '200px',
        ]));
        $this->assertNull(Html::cssStyleFromArray([]));
    }

    public function testCssStyleToArray(): void
    {
        $this->assertSame([
            'width' => '100px',
            'height' => '200px',
        ], Html::cssStyleToArray('width: 100px; height: 200px;'));
        $this->assertSame([], Html::cssStyleToArray('  '));
    }

    public function testAddCssStyle(): void
    {
        $options = ['style' => 'width: 100px; height: 200px;'];
        Html::addCssStyle($options, 'width: 110px; color: red;');
        $this->assertSame('width: 110px; height: 200px; color: red;', $options['style']);

        $options = ['style' => 'width: 100px; height: 200px;'];
        Html::addCssStyle($options, ['width' => '110px', 'color' => 'red']);
        $this->assertSame('width: 110px; height: 200px; color: red;', $options['style']);

        $options = ['style' => 'width: 100px; height: 200px;'];
        Html::addCssStyle($options, 'width: 110px; color: red;', false);
        $this->assertSame('width: 100px; height: 200px; color: red;', $options['style']);

        $options = [];
        Html::addCssStyle($options, 'width: 110px; color: red;');
        $this->assertSame('width: 110px; color: red;', $options['style']);

        $options = [];
        Html::addCssStyle($options, 'width: 110px; color: red;', false);
        $this->assertSame('width: 110px; color: red;', $options['style']);

        $options = [
            'style' => [
                'width' => '100px',
            ],
        ];
        Html::addCssStyle($options, ['color' => 'red'], false);
        $this->assertSame('width: 100px; color: red;', $options['style']);
    }

    public function testRemoveCssStyle(): void
    {
        $options = ['style' => 'width: 110px; height: 200px; color: red;'];
        Html::removeCssStyle($options, 'width');
        $this->assertSame('height: 200px; color: red;', $options['style']);
        Html::removeCssStyle($options, ['height']);
        $this->assertSame('color: red;', $options['style']);
        Html::removeCssStyle($options, ['color', 'background']);
        $this->assertNull($options['style']);

        $options = [];
        Html::removeCssStyle($options, ['color', 'background']);
        $this->assertNotTrue(array_key_exists('style', $options));
        $options = [
            'style' => [
                'color' => 'red',
                'width' => '100px',
            ],
        ];
        Html::removeCssStyle($options, ['color']);
        $this->assertSame('width: 100px;', $options['style']);
    }

    public function testBooleanAttributes(): void
    {
        $this->assertSame('<input type="email" name="mail">', Html::input('email', 'mail', null, ['required' => false]));
        $this->assertSame('<input type="email" name="mail" required>', Html::input('email', 'mail', null, ['required' => true]));
        $this->assertSame('<input type="email" name="mail" required="hi">', Html::input('email', 'mail', null, ['required' => 'hi']));
    }

    public function testDataAttributes(): void
    {
        $this->assertSame('<link src="xyz" data-a="1" data-b="c">', Html::tag('link', '', ['src' => 'xyz', 'data' => ['a' => 1, 'b' => 'c']]));
        $this->assertSame('<link src="xyz" ng-a="1" ng-b="c">', Html::tag('link', '', ['src' => 'xyz', 'ng' => ['a' => 1, 'b' => 'c']]));
        $this->assertSame('<link src="xyz" data-ng-a="1" data-ng-b="c">', Html::tag('link', '', ['src' => 'xyz', 'data-ng' => ['a' => 1, 'b' => 'c']]));
        $this->assertSame('<link src=\'{"a":1,"b":"It\\u0027s"}\'>', Html::tag('link', '', ['src' => ['a' => 1, 'b' => "It's"]]));
    }

    private function getDataItems(): array
    {
        return [
            'value1' => 'text1',
            'value2' => 'text2',
        ];
    }

    private function getDataItems2(): array
    {
        return [
            'value1<>' => 'text1<>',
            'value  2' => 'text  2',
        ];
    }

    private function getDataItems3(): array
    {
        return [
            'zero',
            'one',
            'value3' => 'text3',
        ];
    }

    /**
     * Data provider for {@see testActiveTextInput()}.
     * @return array test data
     */
    public function dataProviderActiveTextInput(): array
    {
        return [
            [
                'some text',
                [],
                '<input type="text" id="htmltestmodel-name" name="HtmlTestModel[name]" value="some text">',
            ],
            [
                '',
                [
                    'maxlength' => true,
                ],
                '<input type="text" id="htmltestmodel-name" name="HtmlTestModel[name]" value="" maxlength="100">',
            ],
            [
                '',
                [
                    'maxlength' => 99,
                ],
                '<input type="text" id="htmltestmodel-name" name="HtmlTestModel[name]" value="" maxlength="99">',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActiveTextInput
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActiveTextInput($value, array $options, $expectedHtml): void
    {
        $model = new HtmlTestModel();
        $model->name = $value;
        $this->assertSame($expectedHtml, Html::activeTextInput($model, 'name', $options));
    }

    /**
     * Data provider for {@see testActivePasswordInput()}.
     * @return array test data
     */
    public function dataProviderActivePasswordInput(): array
    {
        return [
            [
                'some text',
                [],
                '<input type="password" id="htmltestmodel-name" name="HtmlTestModel[name]" value="some text">',
            ],
            [
                '',
                [
                    'maxlength' => true,
                ],
                '<input type="password" id="htmltestmodel-name" name="HtmlTestModel[name]" value="" maxlength="100">',
            ],
            [
                '',
                [
                    'maxlength' => 99,
                ],
                '<input type="password" id="htmltestmodel-name" name="HtmlTestModel[name]" value="" maxlength="99">',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActivePasswordInput
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActivePasswordInput($value, array $options, $expectedHtml): void
    {
        $model = new HtmlTestModel();
        $model->name = $value;
        $this->assertSame($expectedHtml, Html::activePasswordInput($model, 'name', $options));
    }

    public function errorSummaryDataProvider(): array
    {
        return [
            [
                'ok',
                [],
                '<div style="display:none"><p>Please fix the following errors:</p><ul></ul></div>',
            ],
            [
                'ok',
                ['header' => 'Custom header', 'footer' => 'Custom footer', 'style' => 'color: red'],
                '<div style="color: red; display:none">Custom header<ul></ul>Custom footer</div>',
            ],
            [
                str_repeat('long_string', 60),
                [],
                '<div><p>Please fix the following errors:</p><ul><li>Name should contain at most 100 characters.</li></ul></div>',
            ],
            [
                'not_an_integer',
                [],
                '<div><p>Please fix the following errors:</p><ul><li>Error message. Here are some chars: &lt; &gt;</li></ul></div>',
                static function ($model) {
                    /* @var DynamicModel $model */
                    $model->addError('name', 'Error message. Here are some chars: < >');
                },
            ],
            [
                'not_an_integer',
                ['encode' => false],
                '<div><p>Please fix the following errors:</p><ul><li>Error message. Here are some chars: < ></li></ul></div>',
                static function ($model) {
                    /* @var DynamicModel $model */
                    $model->addError('name', 'Error message. Here are some chars: < >');
                },
            ],
            [
                str_repeat('long_string', 60),
                [],
                '<div><p>Please fix the following errors:</p><ul><li>Error message. Here are some chars: &lt; &gt;</li></ul></div>',
                static function ($model) {
                    /* @var DynamicModel $model */
                    $model->addError('name', 'Error message. Here are some chars: < >');
                },
            ],
            [
                'not_an_integer',
                ['showAllErrors' => true],
                '<div><p>Please fix the following errors:</p><ul><li>Error message. Here are some chars: &lt; &gt;</li>
<li>Error message. Here are even more chars: &quot;&quot;</li></ul></div>',
                static function ($model) {
                    /* @var DynamicModel $model */
                    $model->addError('name', 'Error message. Here are some chars: < >');
                    $model->addError('name', 'Error message. Here are even more chars: ""');
                },
            ],
        ];
    }

    /**
     * @dataProvider errorSummaryDataProvider
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     * @param \Closure $beforeValidate
     */
    public function testErrorSummary($value, array $options, $expectedHtml, $beforeValidate = null): void
    {
        $model = new HtmlTestModel();
        $model->name = $value;
        if ($beforeValidate !== null) {
            $beforeValidate($model);
        }
        $model->validate(null, false);

        $this->assertSameWithoutLE($expectedHtml, Html::errorSummary($model, $options));
    }

    public function testError(): void
    {
        $model = new HtmlTestModel();
        $model->validate();
        $this->assertSame(
            '<div>Name cannot be blank.</div>',
            Html::error($model, 'name'),
            'Default error message after calling $model->getFirstError()'
        );

        $this->assertSame(
            '<div>this is custom error message</div>',
            Html::error($model, 'name', ['errorSource' => [$model, 'customError']]),
            'Custom error message generated by callback'
        );
        $this->assertSame(
            '<div>Error in yii\tests\framework\helpers\HtmlTestModel - name</div>',
            Html::error($model, 'name', ['errorSource' => static function ($model, $attribute) {
                return 'Error in ' . get_class($model) . ' - ' . $attribute;
            }]),
            'Custom error message generated by closure'
        );
    }

    /**
     * Test that attributes that output same errors, return unique message error
     * @see https://github.com/yiisoft/yii2/pull/15859
     */
    public function testCollectError(): void
    {
        $model = new DynamicModel(['attr1' => 'attr1', 'attr2' => 'attr2']);

        $model->addError('attr1', 'error1');
        $model->addError('attr1', 'error2');
        $model->addError('attr2', 'error1');

        $this->assertSameWithoutLE(
            '<div><p>Please fix the following errors:</p><ul><li>error1</li>
<li>error2</li></ul></div>',
            Html::errorSummary($model, ['showAllErrors' => true])
        );
    }

    /**
     * Data provider for {@see testActiveTextArea()}.
     * @return array test data
     */
    public function dataProviderActiveTextArea(): array
    {
        return [
            [
                'some text',
                [],
                '<textarea id="htmltestmodel-description" name="HtmlTestModel[description]">some text</textarea>',
            ],
            [
                'some text',
                [
                    'maxlength' => true,
                ],
                '<textarea id="htmltestmodel-description" name="HtmlTestModel[description]" maxlength="500">some text</textarea>',
            ],
            [
                'some text',
                [
                    'maxlength' => 99,
                ],
                '<textarea id="htmltestmodel-description" name="HtmlTestModel[description]" maxlength="99">some text</textarea>',
            ],
            [
                'some text',
                [
                    'value' => 'override text',
                ],
                '<textarea id="htmltestmodel-description" name="HtmlTestModel[description]">override text</textarea>',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActiveTextArea
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActiveTextArea($value, array $options, $expectedHtml): void
    {
        $model = new HtmlTestModel();
        $model->description = $value;
        $this->assertSame($expectedHtml, Html::activeTextarea($model, 'description', $options));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10078
     */
    public function testCsrfDisable(): void
    {
        Yii::getApp()->request->enableCsrfValidation = true;
        Yii::getApp()->request->cookieValidationKey = 'foobar';

        $csrfForm = Html::beginForm('/index.php', 'post', ['id' => 'mycsrfform']);
        $this->assertSame(
            '<form id="mycsrfform" action="/index.php" method="post">'
            . "\n" . '<input type="hidden" name="_csrf" value="' . Yii::getApp()->request->getCsrfToken() . '">',
            $csrfForm
        );

        $noCsrfForm = Html::beginForm('/index.php', 'post', ['csrf' => false, 'id' => 'myform']);
        $this->assertSame('<form id="myform" action="/index.php" method="post">', $noCsrfForm);
    }

    /**
     * Data provider for {@see testActiveRadio()}.
     * @return array test data
     */
    public function dataProviderActiveRadio(): array
    {
        return [
            [
                true,
                [],
                '<input type="hidden" name="HtmlTestModel[radio]" value="0"><label><input type="radio" id="htmltestmodel-radio" name="HtmlTestModel[radio]" value="1" checked> Radio</label>',
            ],
            [
                true,
                ['uncheck' => false],
                '<label><input type="radio" id="htmltestmodel-radio" name="HtmlTestModel[radio]" value="1" checked> Radio</label>',
            ],
            [
                true,
                ['label' => false],
                '<input type="hidden" name="HtmlTestModel[radio]" value="0"><input type="radio" id="htmltestmodel-radio" name="HtmlTestModel[radio]" value="1" checked>',
            ],
            [
                true,
                ['uncheck' => false, 'label' => false],
                '<input type="radio" id="htmltestmodel-radio" name="HtmlTestModel[radio]" value="1" checked>',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActiveRadio
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActiveRadio($value, array $options, $expectedHtml): void
    {
        $model = new HtmlTestModel();
        $model->radio = $value;
        $this->assertSame($expectedHtml, Html::activeRadio($model, 'radio', $options));
    }

    /**
     * Data provider for {@see testActiveCheckbox()}.
     * @return array test data
     */
    public function dataProviderActiveCheckbox(): array
    {
        return [
            [
                true,
                [],
                '<input type="hidden" name="HtmlTestModel[checkbox]" value="0"><label><input type="checkbox" id="htmltestmodel-checkbox" name="HtmlTestModel[checkbox]" value="1" checked> Checkbox</label>',
            ],
            [
                true,
                ['uncheck' => false],
                '<label><input type="checkbox" id="htmltestmodel-checkbox" name="HtmlTestModel[checkbox]" value="1" checked> Checkbox</label>',
            ],
            [
                true,
                ['label' => false],
                '<input type="hidden" name="HtmlTestModel[checkbox]" value="0"><input type="checkbox" id="htmltestmodel-checkbox" name="HtmlTestModel[checkbox]" value="1" checked>',
            ],
            [
                true,
                ['uncheck' => false, 'label' => false],
                '<input type="checkbox" id="htmltestmodel-checkbox" name="HtmlTestModel[checkbox]" value="1" checked>',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderActiveCheckbox
     *
     * @param string $value
     * @param array $options
     * @param string $expectedHtml
     */
    public function testActiveCheckbox($value, array $options, $expectedHtml): void
    {
        $model = new HtmlTestModel();
        $model->checkbox = $value;
        $this->assertSame($expectedHtml, Html::activeCheckbox($model, 'checkbox', $options));
    }

    /**
     * Data provider for {@see testAttributeNameValidation()}.
     * @return array test data
     */
    public function validAttributeNamesProvider(): array
    {
        $data = [
            ['asd]asdf.asdfa[asdfa', 'asdf.asdfa'],
            ['a', 'a'],
            ['[0]a', 'a'],
            ['a[0]', 'a'],
            ['[0]a[0]', 'a'],
            ['[0]a.[0]', 'a.'],
        ];

        if (getenv('TRAVIS_PHP_VERSION') !== 'nightly') {
            array_push($data, ['ä', 'ä'], ['ä', 'ä'], ['asdf]öáöio..[asdfasdf', 'öáöio..'], ['öáöio', 'öáöio'], ['[0]test.ööößß.d', 'test.ööößß.d'], ['ИІК', 'ИІК'], [']ИІК[', 'ИІК'], ['[0]ИІК[0]', 'ИІК']);
        } else {
            $this->markTestIncomplete("Unicode characters check skipped for 'nightly' PHP version because \w does not work with these as expected. Check later with stable version.");
        }

        return $data;
    }

    /**
     * Data provider for {@see testAttributeNameValidation()}.
     * @return array test data
     */
    public function invalidAttributeNamesProvider(): array
    {
        return [
            ['. ..'],
            ['a +b'],
            ['a,b'],
        ];
    }

    /**
     * @dataProvider validAttributeNamesProvider
     *
     * @param string $name
     * @param string $expected
     */
    public function testAttributeNameValidation($name, $expected): void
    {
        if (!isset($expected)) {
            $this->expectException('yii\exceptions\InvalidArgumentException');
            Html::getAttributeName($name);
        } else {
            $this->assertSame($expected, Html::getAttributeName($name));
        }
    }

    /**
     * @dataProvider invalidAttributeNamesProvider
     *
     * @param string $name
     */
    public function testAttributeNameException($name): void
    {
        $this->expectException('yii\exceptions\InvalidArgumentException');
        Html::getAttributeName($name);
    }

    public function testActiveFileInput(): void
    {
        $expected = '<input type="hidden" name="foo" value=""><input type="file" id="htmltestmodel-types" name="foo">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['name' => 'foo']);
        $this->assertSameWithoutLE($expected, $actual);

        $expected = '<input type="hidden" name="foo" value="" disabled><input type="file" id="htmltestmodel-types" name="foo" disabled>';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['name' => 'foo', 'disabled' => true]);
        $this->assertSameWithoutLE($expected, $actual);

        $expected = '<input type="hidden" id="specific-id" name="foo" value=""><input type="file" id="htmltestmodel-types" name="foo">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['name' => 'foo', 'hiddenOptions' => ['id' => 'specific-id']]);
        $this->assertSameWithoutLE($expected, $actual);

        $expected = '<input type="hidden" id="specific-id" name="HtmlTestModel[types]" value=""><input type="file" id="htmltestmodel-types" name="HtmlTestModel[types]">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['hiddenOptions' => ['id' => 'specific-id']]);
        $this->assertSameWithoutLE($expected, $actual);

        $expected = '<input type="hidden" name="HtmlTestModel[types]" value=""><input type="file" id="htmltestmodel-types" name="HtmlTestModel[types]">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['hiddenOptions' => []]);
        $this->assertSameWithoutLE($expected, $actual);

        $expected = '<input type="hidden" name="foo" value=""><input type="file" id="htmltestmodel-types" name="foo">';
        $model = new HtmlTestModel();
        $actual = Html::activeFileInput($model, 'types', ['name' => 'foo', 'hiddenOptions' => []]);
        $this->assertSameWithoutLE($expected, $actual);
    }

    /**
     * @expectedException \yii\exceptions\InvalidArgumentException
     * @expectedExceptionMessage Attribute name must contain word characters only.
     */
    public function testGetAttributeValueInvalidArgumentException(): void
    {
        $model = new HtmlTestModel();
        Html::getAttributeValue($model, '-');
    }

    public function testGetAttributeValue(): void
    {
        $model = new HtmlTestModel();

        $expected = null;
        $actual = Html::getAttributeValue($model, 'types');
        $this->assertSame($expected, $actual);

        $activeRecord = $this->createMock(\Yiisoft\ActiveRecord\ActiveRecordInterface::class);
        $activeRecord->method('getPrimaryKey')->willReturn(1);
        $model->types = $activeRecord;

        $expected = 1;
        $actual = Html::getAttributeValue($model, 'types');
        $this->assertSame($expected, $actual);

        $model->types = [
            $activeRecord,
        ];

        $expected = [1];
        $actual = Html::getAttributeValue($model, 'types');
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \yii\exceptions\InvalidArgumentException
     * @expectedExceptionMessage Attribute name must contain word characters only.
     */
    public function testGetInputNameInvalidArgumentExceptionAttribute(): void
    {
        $model = new HtmlTestModel();
        Html::getInputName($model, '-');
    }

    /**
     * @expectedException \yii\exceptions\InvalidArgumentException
     * @expectedExceptionMessageRegExp /(.*)formName\(\) cannot be empty for tabular inputs.$/
     */
    public function testGetInputNameInvalidArgumentExceptionFormName(): void
    {
        $model = $this->createMock(\yii\base\Model::class);
        $model->method('formName')->willReturn('');
        Html::getInputName($model, '[foo]bar');
    }

    public function testGetInputName(): void
    {
        $model = $this->createMock(\yii\base\Model::class);
        $model->method('formName')->willReturn('');
        $expected = 'types';
        $actual = Html::getInputName($model, 'types');
        $this->assertSame($expected, $actual);
    }


    public function testEscapeJsRegularExpression(): void
    {
        $expected = '/[a-z0-9-]+/';
        $actual = Html::escapeJsRegularExpression('([a-z0-9-]+)');
        $this->assertSame($expected, $actual);

        $expected = '/([a-z0-9-]+)/gim';
        $actual = Html::escapeJsRegularExpression('/([a-z0-9-]+)/Ugimex');
        $this->assertSame($expected, $actual);
    }

    public function testActiveDropDownList(): void
    {
        $expected = <<<'HTML'
<input type="hidden" name="HtmlTestModel[types]" value=""><select id="htmltestmodel-types" name="HtmlTestModel[types][]" multiple="true" size="4">

</select>
HTML;
        $model = new HtmlTestModel();
        $actual = Html::activeDropDownList($model, 'types', [], ['multiple' => 'true']);
        $this->assertSameWithoutLE($expected, $actual);
    }

    public function testActiveCheckboxList(): void
    {
        $model = new HtmlTestModel();

        $expected = <<<'HTML'
<input type="hidden" name="HtmlTestModel[types]" value=""><div id="htmltestmodel-types"><label><input type="radio" name="HtmlTestModel[types]" value="0"> foo</label></div>
HTML;
        $actual = Html::activeRadioList($model, 'types', ['foo']);
        $this->assertSameWithoutLE($expected, $actual);
    }

    public function testActiveRadioList(): void
    {
        $model = new HtmlTestModel();

        $expected = <<<'HTML'
<input type="hidden" name="HtmlTestModel[types]" value=""><div id="htmltestmodel-types"><label><input type="checkbox" name="HtmlTestModel[types][]" value="0"> foo</label></div>
HTML;
        $actual = Html::activeCheckboxList($model, 'types', ['foo']);
        $this->assertSameWithoutLE($expected, $actual);
    }

    public function testActiveTextInput_placeholderFillFromModel(): void
    {
        $model = new HtmlTestModel();

        $html = Html::activeTextInput($model, 'name', ['placeholder' => true]);

        $this->assertContains('placeholder="Name"', $html);
    }

    public function testActiveTextInput_customPlaceholder(): void
    {
        $model = new HtmlTestModel();

        $html = Html::activeTextInput($model, 'name', ['placeholder' => 'Custom placeholder']);

        $this->assertContains('placeholder="Custom placeholder"', $html);
    }

    public function testActiveTextInput_placeholderFillFromModelTabular(): void
    {
        $model = new HtmlTestModel();

        $html = Html::activeTextInput($model, '[0]name', ['placeholder' => true]);

        $this->assertContains('placeholder="Name"', $html);
    }
}