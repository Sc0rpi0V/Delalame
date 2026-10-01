<?php

namespace App\Controller\Admin;

use App\Entity\GalleryImage;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Symfony\Component\Validator\Constraints as Assert;

class GalleryImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return GalleryImage::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('title', 'Titre');
        yield ChoiceField::new('category', 'Catégorie')->setChoices(GalleryImage::CATEGORIES);
        yield Field::new('imageFile', 'Image')
            ->setFormType(\Vich\UploaderBundle\Form\Type\VichImageType::class)
            ->setFormTypeOptions([
                'constraints' => [
                    new Assert\File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Formats acceptés : JPG, PNG, WebP',
                    ),
                ],
            ])
            ->onlyOnForms();
        yield ImageField::new('imageName', 'Aperçu')
            ->setBasePath('/uploads/gallery')
            ->onlyOnIndex();
        yield TextareaField::new('description', 'Description')->setRequired(false);
        yield TextField::new('altText', 'Texte alternatif (accessibilité, SEO)')
            ->setRequired(false)
            ->setHelp('Décrit l\'image pour les malvoyants et les moteurs de recherche. Si vide, le titre est utilisé.')
            ->hideOnIndex();
        yield BooleanField::new('published', 'Publié');
        yield BooleanField::new('featured', 'À la une')
            ->setHelp('Affichée en premier sur la page réalisations. Maximum 5 images.');
        yield DateTimeField::new('createdAt', 'Date')->onlyOnIndex();
    }

    public function persistEntity(EntityManagerInterface $em, mixed $entity): void
    {
        $this->enforceFeaturedLimit($em, $entity);
        parent::persistEntity($em, $entity);
    }

    public function updateEntity(EntityManagerInterface $em, mixed $entity): void
    {
        $this->enforceFeaturedLimit($em, $entity);
        parent::updateEntity($em, $entity);
    }

    private function enforceFeaturedLimit(EntityManagerInterface $em, mixed $entity): void
    {
        if (!$entity instanceof GalleryImage || !$entity->isFeatured()) {
            return;
        }

        $currentFeatured = $em->getRepository(GalleryImage::class)->count([
            'featured' => true,
        ]);

        // Si cette entité est déjà en base et déjà featured, elle compte déjà
        $alreadyFeatured = $entity->getId() !== null
            && $em->getRepository(GalleryImage::class)->find($entity->getId())?->isFeatured();

        $effectiveCount = $alreadyFeatured ? $currentFeatured : $currentFeatured + 1;

        if ($effectiveCount > 5) {
            $entity->setFeatured(false);
            $this->addFlash('warning', 'Limite de 5 images "À la une" atteinte. Cette image ne sera pas mise en avant.');
        }
    }
}
