<?php

namespace App\Tests\Controller;

use App\Entity\Excursion;
use App\Repository\ExcursionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExcursionControllerTest extends WebTestCase{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $excursionRepository;
    private string $path = '/excursion/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->excursionRepository = $this->manager->getRepository(Excursion::class);

        foreach ($this->excursionRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Excursion index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'excursion[nom]' => 'Testing',
            'excursion[lieu]' => 'Testing',
            'excursion[prix]' => 'Testing',
            'excursion[date_depart]' => 'Testing',
            'excursion[date_retour]' => 'Testing',
            'excursion[disponibilite]' => 'Testing',
            'excursion[description]' => 'Testing',
            'excursion[nombre_max_participants]' => 'Testing',
            'excursion[image]' => 'Testing',
            'excursion[ville_depart]' => 'Testing',
            'excursion[lieuDepart]' => 'Testing',
            'excursion[destination]' => 'Testing',
            'excursion[nomGuide]' => 'Testing',
            'excursion[nbPlaces]' => 'Testing',
            'excursion[guide]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->excursionRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Excursion();
        $fixture->setNom('My Title');
        $fixture->setLieu('My Title');
        $fixture->setPrix('My Title');
        $fixture->setDate_depart('My Title');
        $fixture->setDate_retour('My Title');
        $fixture->setDisponibilite('My Title');
        $fixture->setDescription('My Title');
        $fixture->setNombre_max_participants('My Title');
        $fixture->setImage('My Title');
        $fixture->setVille_depart('My Title');
        $fixture->setLieuDepart('My Title');
        $fixture->setDestination('My Title');
        $fixture->setNomGuide('My Title');
        $fixture->setNbPlaces('My Title');
        $fixture->setGuide('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Excursion');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Excursion();
        $fixture->setNom('Value');
        $fixture->setLieu('Value');
        $fixture->setPrix('Value');
        $fixture->setDate_depart('Value');
        $fixture->setDate_retour('Value');
        $fixture->setDisponibilite('Value');
        $fixture->setDescription('Value');
        $fixture->setNombre_max_participants('Value');
        $fixture->setImage('Value');
        $fixture->setVille_depart('Value');
        $fixture->setLieuDepart('Value');
        $fixture->setDestination('Value');
        $fixture->setNomGuide('Value');
        $fixture->setNbPlaces('Value');
        $fixture->setGuide('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'excursion[nom]' => 'Something New',
            'excursion[lieu]' => 'Something New',
            'excursion[prix]' => 'Something New',
            'excursion[date_depart]' => 'Something New',
            'excursion[date_retour]' => 'Something New',
            'excursion[disponibilite]' => 'Something New',
            'excursion[description]' => 'Something New',
            'excursion[nombre_max_participants]' => 'Something New',
            'excursion[image]' => 'Something New',
            'excursion[ville_depart]' => 'Something New',
            'excursion[lieuDepart]' => 'Something New',
            'excursion[destination]' => 'Something New',
            'excursion[nomGuide]' => 'Something New',
            'excursion[nbPlaces]' => 'Something New',
            'excursion[guide]' => 'Something New',
        ]);

        self::assertResponseRedirects('/excursion/');

        $fixture = $this->excursionRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getLieu());
        self::assertSame('Something New', $fixture[0]->getPrix());
        self::assertSame('Something New', $fixture[0]->getDate_depart());
        self::assertSame('Something New', $fixture[0]->getDate_retour());
        self::assertSame('Something New', $fixture[0]->getDisponibilite());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getNombre_max_participants());
        self::assertSame('Something New', $fixture[0]->getImage());
        self::assertSame('Something New', $fixture[0]->getVille_depart());
        self::assertSame('Something New', $fixture[0]->getLieuDepart());
        self::assertSame('Something New', $fixture[0]->getDestination());
        self::assertSame('Something New', $fixture[0]->getNomGuide());
        self::assertSame('Something New', $fixture[0]->getNbPlaces());
        self::assertSame('Something New', $fixture[0]->getGuide());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Excursion();
        $fixture->setNom('Value');
        $fixture->setLieu('Value');
        $fixture->setPrix('Value');
        $fixture->setDate_depart('Value');
        $fixture->setDate_retour('Value');
        $fixture->setDisponibilite('Value');
        $fixture->setDescription('Value');
        $fixture->setNombre_max_participants('Value');
        $fixture->setImage('Value');
        $fixture->setVille_depart('Value');
        $fixture->setLieuDepart('Value');
        $fixture->setDestination('Value');
        $fixture->setNomGuide('Value');
        $fixture->setNbPlaces('Value');
        $fixture->setGuide('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/excursion/');
        self::assertSame(0, $this->excursionRepository->count([]));
    }
}
