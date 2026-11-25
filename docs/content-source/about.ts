import type { ComponentType } from 'react';
import { BookOpen, Compass, Cpu } from 'lucide-react';

export type AboutContentSection = {
  readonly id: string;
  readonly Icon: ComponentType<{ size?: number; className?: string }>;
  readonly heading: string;
  readonly paragraphs: readonly string[];
};

export type AboutProfileFact = {
  readonly label: string;
  readonly value: string;
  readonly tone: 'success' | 'accent';
};

export const ABOUT_METADATA = {
  title: 'About',
  description: 'Learn more about Nathan Dulkis, web developer and systems thinker.',
} as const;

export const ABOUT_HEADER = {
  eyebrow: 'About',
  title: 'Web developer. Systems thinker.',
  description:
    'I like the parts of software where correctness, reliability, and practical tradeoffs all meet.',
} as const;

export const ABOUT_PROFILE = {
  name: 'Nathan Dulkis',
  role: 'Web developer @ Hounder',
  imageSrc: '/headshot.jpg',
  imageAlt: 'Nathan Dulkis',
  initials: 'ND',
  status: 'Online',
  facts: [
    {
      label: 'Core stack',
      value: 'PHP, Drupal, WordPress',
      tone: 'accent',
    },
    {
      label: 'Work style',
      value: 'In-office',
      tone: 'accent',
    },
    {
      label: 'Current focus',
      value: 'Reliable systems',
      tone: 'success',
    },
  ],
} as const satisfies {
  readonly name: string;
  readonly role: string;
  readonly imageSrc: string;
  readonly imageAlt: string;
  readonly initials: string;
  readonly status: string;
  readonly facts: readonly AboutProfileFact[];
};

export const CONTENT_SECTIONS = [
  {
    id: 'background',
    Icon: Compass,
    heading: 'Background',
    paragraphs: [
      "I'm a web developer who cares about building systems that hold up: ones that stay correct under load, degrade gracefully when something breaks, and remain legible to the next developer who has to work with them.",
      'My instinct is always to understand the constraints before reaching for a solution. Good software engineering, to me, is mostly good reasoning about trade-offs.',
    ],
  },
  {
    id: 'journey',
    Icon: BookOpen,
    heading: 'How I Got Here',
    paragraphs: [
      'I fell into software through an obsession with how things work. Early on it was tinkering with scripts to automate tedious tasks. Slowly, that grew into a genuine interest in how whole systems fit together.',
      "That curiosity turned into my first real job as a developer at Hounder, building client sites for counties, airports, and utilities. Working on software that people depend on to get something done has taught me more than any side project could: the constraints are real, the users are everyone, and the site has to still make sense to whoever maintains it next.",
    ],
  },
  {
    id: 'learning',
    Icon: Cpu,
    heading: 'Current Focus',
    paragraphs: [
      "Right now I'm at Hounder, building client sites in Drupal, WordPress, and Laravel across the full production lifecycle: custom components and page templates, PHP and server-side logic, responsive CSS, content migrations, and accessibility audits. The clients are public agencies, so the QA bar is high and the browser matrix is wide.",
      "Most of what I'm learning is the part that doesn't show up in a tutorial: how to structure a build so a non-technical editor can maintain it for years, how to keep configuration reviewable instead of clicked into a live admin UI, and how to reproduce a production problem locally before trying to fix it.",
    ],
  },
] satisfies readonly AboutContentSection[];

export const ABOUT_CTA = {
  title: "Let's build something reliable.",
  description: "I'm always open to interesting problems and the people trying to solve them.",
  action: {
    href: '/contact',
    label: 'Contact Me',
  },
} as const;
