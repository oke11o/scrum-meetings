<?php

namespace App\Tests\Unit\Form;

use App\Form\LoginFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class LoginFormTypeTest extends TypeTestCase
{
    public function testSubmitValidData()
    {

        $formData = [
            '_username' => 'test',
            '_password' => 'test2',
        ];

//        $dataToCompare = [];
//        $data = [];
        // $objectToCompare will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(LoginFormType::class);


        // submit the data to the form directly
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());

//        // check that $objectToCompare was modified as expected when the form was submitted
//        $this->assertEquals($data, $dataToCompare);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }

//        $this->assertFalse($form->isValid());
    }
}
