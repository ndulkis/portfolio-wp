import {
  CalendarCheck,
  Gamepad2,
  GraduationCap,
  Spade,
  Store,
} from 'lucide-react';

export type ProjectCategory = 'Full-stack' | 'Backend' | 'Frontend' | 'Tools';

export type Project = {
  readonly id: string;
  readonly title: string;
  readonly description: string;
  readonly tags: readonly string[];
  readonly status: 'live' | 'in-progress' | 'archived';
  readonly category: ProjectCategory;
  readonly Icon: React.ComponentType<{ size?: number; className?: string }>;
  readonly href?: string;
};

export const PROJECTS: readonly Project[] = [
  {
    id: 'uab-blaze-a-brighter-future',
    title: 'UAB — Blaze a Brighter Future',
    description:
      'A fully custom WordPress build for the University of Alabama at Birmingham\'s alumni campaign, showcasing graduate career stories to prospective students. Hand-built theme and page templates with no parent theme or page builder, plus custom blocks and ACF fields so university staff can publish new alumni spotlights without touching code. Responsive across the full browser matrix. Built at Hounder.',
    tags: ['WordPress', 'PHP', 'ACF', 'Custom Theme', 'JavaScript', 'Responsive CSS'],
    status: 'live',
    category: 'Full-stack',
    Icon: GraduationCap,
    href: 'https://sites.uab.edu/blazeabrighterfuture/',
  },
  {
  id: 'blackjack-tutor',
  title: 'Blackjack Tutor',
  description:
    'A PyGame blackjack trainer that evaluates every decision against the mathematically correct basic strategy in real time, tracks accuracy by category, and explains mistakes with rule-based coaching. Full 6-deck engine with splits, doubles, surrender, and insurance, backed by 682 tests at 96% coverage. Created with Joon Rho.',
  tags: ['Python', 'PyGame', 'pytest', 'Ruff'],
  status: 'live',
  category: 'Full-stack',
  Icon: Spade,
  href: 'https://github.com/ndulkis/blackjack-tutor',
  },
  {
    id: 'poke-guess',
    title: 'PokeGuess',
    description:
      'A Pokemon guessing game where players identify Pokemon from silhouettes and clues. Built with React 19 and Vite, styled with Tailwind CSS v4, and routed with React Router v7. Created with Eric Morales.',
    tags: ['React', 'Vite', 'Tailwind CSS', 'React Router'],
    status: 'live',
    category: 'Frontend',
    Icon: Gamepad2,
    href: 'https://github.com/ndulkis/poke-guess',
  },
  {
    id: 'habit-tracker',
    title: 'Habit Tracker',
    description:
      'A command-line tool that generates a professional Excel spreadsheet for tracking daily habits across an entire year. Features interactive CLI prompts for custom habits, monthly sheets with conditional formatting, automated success rate calculations, and a yearly summary dashboard with trend charts. Built with Python and xlsxwriter.',
    tags: ['Python', 'xlsxwriter', 'CLI', 'Excel'],
    status: 'live',
    category: 'Tools',
    Icon: CalendarCheck,
    href: 'https://github.com/ndulkis/habit-tracker',
  },
  {
    id: 'bucket-list-brands',
    title: 'Buck-et List Brands E-Commerce Platform',
    description:
      'An e-commerce storefront for a niche hunting and outdoorsman apparel brand. Early development — still working out the architecture and stack.',
    tags: ['E-Commerce'],
    status: 'in-progress',
    category: 'Full-stack',
    Icon: Store,
  },
];
