<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 17:17
 */

namespace App\Form\BuildingMap;

use App\Entity\AppUser;
use App\Entity\Building;
use App\Entity\BuildingMap;
use App\Entity\FrontendUser;
use App\Entity\Traits\PersonTrait;
use App\Form\Base\BaseAbstractType;
use App\Form\Traits\Address\AddressType;
use App\Form\Traits\Person\PersonType;
use App\Form\Traits\Thing\ThingType;
use App\Form\Traits\User\RegisterType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuildingMapType extends BaseAbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add("thing", ThingType::class, ["inherit_data" => true, "label" => false]);
        $builder->add("file", FileType::class, ["required" => false]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'entity_building_map',
            'data_class' => BuildingMap::class
        ]);
    }
}