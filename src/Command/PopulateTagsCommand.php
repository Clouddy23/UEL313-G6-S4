<?php
// src/Command/PopulateTagsCommand.php
namespace App\Command;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:populate-tags',
    description: 'Populate the database with 20 common web development tags.',
)]
class PopulateTagsCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tags = [
            'HTML', 'CSS', 'JavaScript', 'PHP', 'Symfony', 'Laravel', 'React', 'Vue', 'Angular',
            'Node.js', 'API', 'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'Bootstrap', 'Tailwind', 'Sass', 'Webpack', 'Git'
        ];

        foreach ($tags as $tagName) {
            $tag = new Tag();
            $tag->setName($tagName);
            $this->em->persist($tag);
        }
        $this->em->flush();

        $output->writeln('20 web development tags have been added!');
        return Command::SUCCESS;
    }
}