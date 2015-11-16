<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CardGenerate extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('series', 'text', array(
            'label' => 'Серия',
            'attr' => array('placeholder' => 'Серия'),
            'required' => true
        ));

        $builder->add('expiryPeriod', 'choice', array('label' => 'Срок окончания активности',
            'placeholder' => '',
            'mapped' => false,
            'choices' => array(
                '1' => '1 месяц',
                '6' => '6 месяцев',
                '12' => '1 год',
            ),
            'required' => true
        ));

        $builder->add('amount', 'integer', array(
            'label' => 'Количество карт',
            'mapped' => false,
            'required' => true,
            'attr' => array(
                'min' => 1,
                'max' => 50
            )
        ));

        $builder->add('generate', 'submit', array(
            'attr' => array(
                'class' => 'btn btn-primary'
            ),
            'label' => 'Сгенерировать карты'
        ));
    }

    function __construct(array $options = array()) {
        $this->options = $options;
    }

    public function getName() {
        return 'card';
    }

}
