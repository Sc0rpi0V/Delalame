<?php

namespace App\Controller\Admin;

use App\Entity\Service;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ServiceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Service::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['position' => 'ASC', 'id' => 'ASC'])
            ->setSearchFields(['name', 'reference'])
            ->setPageTitle('index', 'Services')
            ->setPageTitle('new', 'Nouveau service')
            ->setPageTitle('edit', fn(Service $s) => 'Modifier : ' . $s->getName())
            ->setHelp('index', 'Services affichés sur la page « Nos services ». Le bouton de chaque service ouvre la messagerie du visiteur avec un mail pré-rempli : destinataire = email des Coordonnées, objet = référence du service, corps = message pré-rédigé ci-dessous.');
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('Présentation sur la page Services');
        yield TextField::new('icon', 'Icône')->setColumns(2)->setHelp('Un emoji');
        yield TextField::new('name', 'Nom')->setColumns(7);
        yield TextField::new('reference', 'Référence')->setColumns(3)->setHelp('Ex. FEN-01 — reprise dans l\'objet du mail');
        yield TextareaField::new('intro', 'Introduction')->onlyOnForms()->setColumns(12);
        yield CollectionField::new('points', 'Points clés')
            ->onlyOnForms()
            ->setEntryType(TextType::class)
            ->allowAdd()
            ->allowDelete()
            ->setColumns(12);

        yield FormField::addFieldset('Mail de demande de devis');
        yield TextareaField::new('mailBody', 'Message pré-rédigé')
            ->onlyOnForms()
            ->setNumOfRows(10)
            ->setColumns(12)
            ->setHelp('Texte brut. Le visiteur pourra compléter sous ce message (une zone « Informations complémentaires » est ajoutée automatiquement à la suite).');

        yield FormField::addFieldset('Affichage');
        yield IntegerField::new('position', 'Ordre')->setColumns(2)->setHelp('Plus petit = affiché en premier');
        yield BooleanField::new('published', 'Publié')->setColumns(2)->renderAsSwitch(true);
    }
}
