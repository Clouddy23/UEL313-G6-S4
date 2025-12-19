<?php
// src/Command/PopulateLinksCommand.php
namespace App\Command;

use App\Entity\Link;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:populate-links',
    description: 'Populate the database with 30 web development links.',
)]
class PopulateLinksCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get the first user (or adapt as needed)
        $user = $this->em->getRepository(User::class)->findOneBy([]);
        if (!$user) {
            $output->writeln('<error>No user found. Please create a user first.</error>');
            return Command::FAILURE;
        }

        $links = [
            ['MDN Web Docs', 'https://developer.mozilla.org/', 'Comprehensive web standards documentation.'],
            ['W3Schools', 'https://www.w3schools.com/', 'Web tutorials and references.'],
            ['freeCodeCamp', 'https://www.freecodecamp.org/', 'Learn to code for free.'],
            ['CSS-Tricks', 'https://css-tricks.com/', 'Tips and tricks for CSS and frontend.'],
            ['Smashing Magazine', 'https://www.smashingmagazine.com/', 'Web design and development magazine.'],
            ['A List Apart', 'https://alistapart.com/', 'Explores the design, development, and meaning of web content.'],
            ['Stack Overflow', 'https://stackoverflow.com/', 'Programming Q&A site.'],
            ['SitePoint', 'https://www.sitepoint.com/', 'Web development tutorials and news.'],
            ['Scotch.io', 'https://scotch.io/', 'Web development tutorials.'],
            ['CodePen', 'https://codepen.io/', 'Social development environment for front-end designers and developers.'],
            ['GitHub', 'https://github.com/', 'Code hosting platform for version control and collaboration.'],
            ['Frontend Mentor', 'https://www.frontendmentor.io/', 'Real-world front-end challenges.'],
            ['Hacker News', 'https://news.ycombinator.com/', 'Social news website focusing on computer science and entrepreneurship.'],
            ['Reddit r/webdev', 'https://www.reddit.com/r/webdev/', 'Web development community.'],
            ['Reddit r/frontend', 'https://www.reddit.com/r/frontend/', 'Frontend development community.'],
            ['Reddit r/javascript', 'https://www.reddit.com/r/javascript/', 'JavaScript community.'],
            ['JavaScript Info', 'https://javascript.info/', 'Modern JavaScript tutorials.'],
            ['ReactJS', 'https://reactjs.org/', 'Official React documentation.'],
            ['VueJS', 'https://vuejs.org/', 'Official Vue.js documentation.'],
            ['Angular', 'https://angular.io/', 'Official Angular documentation.'],
            ['Symfony', 'https://symfony.com/', 'Official Symfony documentation.'],
            ['Laravel', 'https://laravel.com/', 'Official Laravel documentation.'],
            ['Django', 'https://www.djangoproject.com/', 'Official Django documentation.'],
            ['Ruby on Rails', 'https://rubyonrails.org/', 'Official Ruby on Rails documentation.'],
            ['Bootstrap', 'https://getbootstrap.com/', 'Popular CSS framework.'],
            ['Tailwind CSS', 'https://tailwindcss.com/', 'Utility-first CSS framework.'],
            ['Sass', 'https://sass-lang.com/', 'CSS preprocessor.'],
            ['Webpack', 'https://webpack.js.org/', 'Module bundler for JavaScript.'],
            ['npm', 'https://www.npmjs.com/', 'Node package manager.'],
            ['Can I use', 'https://caniuse.com/', 'Browser support tables for web technologies.'],
        ];

        foreach ($links as [$title, $url, $desc]) {
            $link = new Link();
            $link->setTitle($title)
                ->setUrl($url)
                ->setDesc($desc)
                ->setUser($user);
            $this->em->persist($link);
        }
        $this->em->flush();

        $output->writeln('30 web development links have been added!');
        return Command::SUCCESS;
    }
}