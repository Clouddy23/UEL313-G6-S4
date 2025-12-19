<?php
namespace App\Command;

use App\Entity\Link;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:populate-link-tag-associations',
    description: 'Associate tags to links based on title/URL or randomly if no match.',
)]
class PopulateLinkTagAssociationsCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $links = $this->em->getRepository(Link::class)->findAll();
        $tags = $this->em->getRepository(Tag::class)->findAll();

        if (!$links || !$tags) {
            $output->writeln('<error>No links or tags found. Populate them first.</error>');
            return Command::FAILURE;
        }

        foreach ($links as $link) {
            $assigned = [];
            $title = strtolower($link->getTitle());
            $url = strtolower($link->getUrl());

            // Assign tags whose name appears in the title or URL
            foreach ($tags as $tag) {
                $tagName = strtolower($tag->getName());
                if (str_contains($title, $tagName) || str_contains($url, $tagName)) {
                    $link->addTag($tag);
                    $assigned[] = $tag;
                }
            }

            // If no tag matched, assign 2 random tags
            if (count($assigned) === 0) {
                $randomTags = (array)array_rand($tags, min(2, count($tags)));
                foreach ((array)$randomTags as $idx) {
                    $link->addTag($tags[$idx]);
                }
            }

            $this->em->persist($link);
        }

        $this->em->flush();
        $output->writeln('Links have been associated with tags based on their content!');
        return Command::SUCCESS;
    }
}