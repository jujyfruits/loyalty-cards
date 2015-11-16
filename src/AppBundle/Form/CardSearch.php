<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CardSearch extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('series', 'text', array(
            'label' => 'Серия',
            'attr' => array('placeholder' => 'Серия'),
            'required' => false
        ));

        $builder->add('number', 'text', array(
            'label' => 'Номер',
            'attr' => array('placeholder' => 'Номер'),
            'required' => false
        ));

        $builder->add('issueDate', 'datetime', array('label' => 'Дата выпуска',
            'input' => 'datetime',
            'placeholder' => array('year' => 'Year', 'month' => 'Month', 'day' => 'Day'),
            'required' => false,
            'widget' => 'choice'
        ));

        $builder->add('expiryDate', 'datetime', array('label' => 'Дата окончания активности',
            'input' => 'datetime',
            'placeholder' => array('year' => 'Year', 'month' => 'Month', 'day' => 'Day'),
            'required' => false,
            'widget' => 'choice'
        ));
        $statusList = $options['data']::getStatusList();
        $builder->add('status', 'choice', array(
            'empty_data' => null,
            'choices' => array_values($statusList),
            'required' => false
        ));
        $builder->add('search', 'submit', array(
            'attr' => array(
                'class' => 'btn btn-primary'
            ),
            'label' => 'Поиск'
        ));
    }

    function __construct(array $options = array()) {
        $this->options = $options;
    }

    public function getName() {
        return 'card';
    }

}
