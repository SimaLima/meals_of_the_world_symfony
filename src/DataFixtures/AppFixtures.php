<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Meal;
use App\Entity\Ingredient;
use App\Entity\Tag;
use App\Entity\Category;
use App\Entity\Language;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

class AppFixtures extends Fixture
{
    public $translationRepository;

    public function load(ObjectManager $manager)
    {
        $this->translationRepository = $manager->getRepository('Gedmo\\Translatable\\Entity\\Translation');
        $this->loadLanguages($manager);
        $this->loadTags($manager);
        $this->loadIngredients($manager);
        $this->loadCategories($manager);
        $this->loadMeals($manager);
    }

    private function loadLanguages(ObjectManager $manager): void
    {
        $languages = [
            ['locale' => 'en_US', 'title' => 'English'],
            ['locale' => 'hr_HR', 'title' => 'Croatian'],
            ['locale' => 'de_DE', 'title' => 'German'],
            ['locale' => 'fr_FR', 'title' => 'French'],
        ];

        foreach ($languages as $key => $value) {
            $language = new Language();
            $language->setLocale($value['locale']);
            $language->setTitle($value['title']);
            $manager->persist($language);
        }

        $manager->flush();
    }

    private function loadTags(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $tag = new Tag();
            $tag->setTitle('Tag '.$i.' (en)');
            $tag->setSlug('tag-'.$i);
            $this->translationRepository
                 ->translate($tag, 'title', 'hr_HR', 'Oznaka '.$i.' (hr)')
                 ->translate($tag, 'title', 'de_DE', 'Etikett '.$i.' (de)')
                 ->translate($tag, 'title', 'fr_FR', 'Etiqueter '.$i.' (fr)');
            $manager->persist($tag);
            $this->addReference('tag-'.$i, $tag);
        }

        $manager->flush();
    }

    private function loadIngredients(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setTitle('Ingredient '.$i.' (en)');
            $ingredient->setSlug('ingredient-'.$i);
            $this->translationRepository
                 ->translate($ingredient, 'title', 'hr_HR', 'Sastojak '.$i.' (hr)')
                 ->translate($ingredient, 'title', 'de_DE', 'Zutat '.$i.' (de)')
                 ->translate($ingredient, 'title', 'fr_FR', 'Ingredient '.$i.' (fr)');
            $manager->persist($ingredient);
            $this->addReference('ingredient-'.$i, $ingredient);
        }

        $manager->flush();
    }

    private function loadCategories(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $category = new Category();
            $category->setTitle('Category '.$i.' (en)');
            $category->setSlug('category-'.$i);
            $this->translationRepository
                 ->translate($category, 'title', 'hr_HR', 'Kategorija '.$i.' (hr)')
                 ->translate($category, 'title', 'de_DE', 'Kategorie '.$i.' (de)')
                 ->translate($category, 'title', 'fr_FR', 'Categorie '.$i.' (fr)');
            $manager->persist($category);
            $this->addReference('category-'.$i, $category);
        }

        $manager->flush();
    }

    private function loadMeals(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $meal = new Meal();
            $meal->setTitle('Meal title '.$i.' (en)');
            $meal->setDescription('This is meal description '.$i.'. (en)');
            $meal->setSlug('meal-'.$i);

            // meal-category: every 5th meal doesn't have category
            if ($i % 5 != 0) {
                $meal->setCategory(
                    $manager->merge(
                        $this->getReference('category-'.mt_rand(1,5))
                    )
                );
            }

            // timestamps
            // from 20 till today -> 1 meal/day is created
            $days = 20-$i;
            $created_at = new \DateTime('now-'.$days.' days');
            $updated_at = $created_at;
            $deleted_at = null;

            // every 6th meal is deleted 1 day after creation
            if ($i % 6 == 0) $deleted_at = new \DateTime('now-'.($days-1).' days');

            // every 7th meal is updated 1 day after creation
            if ($i % 7 == 0) $updated_at = new \DateTime('now-'.($days-1).' days');

            $meal->setCreatedAt($created_at);
            $meal->setUpdatedAt($updated_at);
            $meal->setDeletedAt($deleted_at);

            // meal-tags
            for ($j = 1; $j <= mt_rand(1,10); $j++) {
                $meal->addTag(
                    $manager->merge(
                        $this->getReference('tag-'.mt_rand(1,10))
                    )
                );
            }

            // meal-ingredients
            for ($k = 1; $k <= mt_rand(1,10); $k++) {
                $meal->addIngredient(
                    $manager->merge(
                        $this->getReference('ingredient-'.mt_rand(1,10))
                    )
                );
            }

            $this->translationRepository
                 ->translate($meal, 'title', 'hr_HR', 'Naslov jela '.$i.' (hr)')
                 ->translate($meal, 'description', 'hr_HR', 'Ovo je opis jela '.$i.'. (hr)')
                 ->translate($meal, 'title', 'de_DE', 'Mahlzeitentitel '.$i.' (de)')
                 ->translate($meal, 'description', 'de_DE', 'Dies ist die Beschreibung der Mahlzeit '.$i.'. (de)')
                 ->translate($meal, 'title', 'fr_FR', 'Titre du repas '.$i.' (fr)')
                 ->translate($meal, 'description', 'fr_FR', 'Ceci est la description du repas '.$i.'. (fr)');
            $manager->persist($meal);
            $this->addReference('meal-'.$i, $meal);
        }

        $manager->flush();
    }
}
