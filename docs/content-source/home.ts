import type { ComponentType } from 'react';
import {
  Activity,
  Blocks,
  Building2,
  Cloud,
  Code2,
  Database,
  Monitor,
  PanelsTopLeft,
  Rocket,
  TerminalSquare,
} from 'lucide-react';

export type HomeMetric = {
  readonly label: string;
  readonly value: string;
  readonly delta: string;
  readonly Icon: ComponentType<{ size?: number; className?: string }>;
};

export type SkillSystem = {
  readonly title: string;
  readonly Icon: ComponentType<{ size?: number; className?: string }>;
  readonly skills: readonly string[];
};

export type ProfessionalRole = {
  readonly id: string;
  readonly title: string;
  readonly company: string;
  readonly period: string;
  readonly status: 'live' | 'in-progress' | 'archived';
  readonly Icon: ComponentType<{ size?: number; className?: string }>;
  readonly summary: string;
  readonly perspective: string;
  readonly tags: readonly string[];
};

export type DeploymentNode = {
  readonly id: string;
  readonly x: number;
  readonly y: number;
  readonly label: string;
  readonly labelPosition: 'left' | 'right';
};

export type ArchitectureDecision = {
  readonly decision: string;
  readonly why: string;
  readonly tradeoff: string;
};

export type DeploymentArchitecture = {
  readonly title: string;
  readonly status: string;
  readonly nodes: readonly DeploymentNode[];
  readonly connections: readonly (readonly [string, string])[];
  readonly notes: readonly string[];
};

export const HOME_HERO = {
  eyebrow: '// Code. Deploy. Scale. Repeat.',
  title: {
    prefix: 'Building',
    highlight: 'Reliable Web Systems',
  },
  description:
    "I build web applications and the systems behind them. Currently at Hounder, developing in Drupal, WordPress, and Laravel for counties, airports, and utilities — sites where a broken page means someone can't renew a permit.",
  primaryAction: {
    href: '#projects',
    label: 'View Projects',
  },
  secondaryAction: {
    href: '/contact',
    label: 'Contact Me',
  },
} as const;

export const STATUS_ROWS = [
  ['System Status', 'Online'],
  ['Role', 'Web Developer @ Hounder'],
  ['Active Projects', '5'],
  ['Focus', 'Reliable Systems'],
  ['Availability', 'Employed'],
] as const;

export const TERMINAL_LINES = [
  '> initializing portfolio...',
  '> loading projects...',
  '> scanning system status...',
  '> checking availability...',
  '> loading contact links...',
  '> portfolio ready',
] as const;

export const HOME_PANEL_TITLES = {
  status: 'Current Status',
  terminal: 'Terminal // Portfolio Console',
} as const;

export const HOME_ARIA_LABELS = {
  metrics: 'Portfolio metrics',
} as const;

export const METRICS = [
  {
    label: 'Projects Built',
    value: '5',
    delta: '',
    Icon: Rocket,
  },
  {
    label: 'Current Focus',
    value: 'Reliable Systems',
    delta: '',
    Icon: TerminalSquare,
  },
  {
    label: 'Core Stack',
    value: 'PHP · Drupal · WordPress · Laravel',
    delta: '',
    Icon: Blocks,
  },
  {
    label: 'Availability',
    value: 'Employed',
    delta: '',
    Icon: Cloud,
  },
] satisfies readonly HomeMetric[];

export const FEATURED_PROJECTS_SECTION = {
  eyebrow: 'Projects',
  title: 'Featured Projects',
  action: {
    href: '/projects',
    label: 'View all projects',
  },
} as const;

export const PROFESSIONAL_SECTION = {
  eyebrow: 'Experience',
  title: 'Professional Work',
  label: 'Role',
} as const;

export const PROFESSIONAL_ROLES = [
  {
    id: 'hounder',
    title: 'Web Developer',
    company: 'Hounder',
    period: 'April 2026 – Present',
    status: 'live',
    Icon: Building2,
    summary:
      'Client sites in Drupal, WordPress, and Laravel across the full production lifecycle: custom components and page templates, PHP and server-side logic, responsive CSS, migrations, and Lighthouse accessibility audits. The clients are public agencies, so the QA bar is high and the browser matrix is wide.',
    perspective:
      "Different stack than my personal projects, same priorities. A Drupal build for a county and a Next.js storefront both come down to whether it's reliable, whether it's maintainable by the next person, and whether it works for everyone who needs it.",
    tags: ['PHP', 'Drupal', 'WordPress', 'Laravel', 'Twig', 'Accessibility'],
  },
] satisfies readonly ProfessionalRole[];

export const SKILLS_SECTION = {
  eyebrow: 'Skills',
  title: 'Technical Skills',
} as const;

export const SKILL_SYSTEMS = [
  {
    title: 'CMS & PHP',
    Icon: PanelsTopLeft,
    skills: ['Drupal', 'WordPress', 'Laravel', 'PHP', 'Twig', 'Content Migrations'],
  },
  {
    title: 'Frontend',
    Icon: Monitor,
    skills: [
      'Custom Theming & Templates',
      'Semantic HTML',
      'Responsive CSS',
      'JavaScript',
      'React / Next.js',
      'TypeScript',
    ],
  },
  {
    title: 'Backend',
    Icon: Code2,
    skills: [
      'Server-Side Logic',
      'Custom Modules & Plugins',
      'REST APIs & Webhooks',
      'Authentication',
      'Node.js',
      'Python',
    ],
  },
  {
    title: 'Databases',
    Icon: Database,
    skills: [
      'MySQL',
      'SQL Queries & Debugging',
      'Drush & WP-CLI DB Syncs',
      'Environment Sync (Prod → Local)',
      'Schema & Update Hooks',
      'Data Modeling',
    ],
  },
  {
    title: 'Quality & Accessibility',
    Icon: Activity,
    skills: [
      'Accessibility / WCAG',
      'Lighthouse Audits',
      'Cross-Browser QA',
      'Testing',
      'Performance',
      'Security',
    ],
  },
  {
    title: 'Infrastructure',
    Icon: Cloud,
    skills: [
      'Acquia',
      'Pantheon',
      'Laravel Forge',
      'Vercel',
      'Docker',
      'GitHub Actions / CI/CD',
    ],
  },
] satisfies readonly SkillSystem[];

export const DEPLOYMENT_ARCHITECTURE: DeploymentArchitecture = {
  title: 'Deployment Architecture',
  status: 'Active',
  nodes: [
    { id: 'source', x: 10, y: 61, label: 'Source Control', labelPosition: 'right' },
    { id: 'ci', x: 30, y: 34, label: 'CI / CD', labelPosition: 'right' },
    { id: 'deploy', x: 52, y: 48, label: 'Deployment', labelPosition: 'right' },
    { id: 'api', x: 72, y: 27, label: 'API Layer', labelPosition: 'right' },
    { id: 'db', x: 80, y: 68, label: 'Database', labelPosition: 'right' },
    { id: 'cache', x: 44, y: 78, label: 'Cache / Storage', labelPosition: 'right' },
    { id: 'monitoring', x: 38, y: 12, label: 'Monitoring', labelPosition: 'right' },
  ],
  connections: [
    ['source', 'ci'],
    ['ci', 'deploy'],
    ['deploy', 'api'],
    ['deploy', 'db'],
    ['cache', 'deploy'],
    ['deploy', 'monitoring'],
  ],
  notes: [
    'Code moves through CI/CD into a deployment target.',
    'App services connect to APIs, data, storage, and monitoring.',
  ],
};

export const OPTIMIZATION_PRIORITIES = {
  title: 'What I Optimize For',
  status: 'Focus',
  items: [
    ['Reliability', 'Systems that fail clearly and recover cleanly.'],
    ['Performance', 'Fast interfaces, efficient APIs, and tuned queries.'],
    ['Maintainability', 'Readable code, focused tests, and simple ownership.'],
    ['Security', 'Safer auth, validation, and data handling by default.'],
  ],
} as const;

export const ARCHITECTURE_SECTION = {
  eyebrow: 'Engineering Judgment',
  title: 'Architecture Decisions',
  description:
    'A few examples of how I think through technical tradeoffs when building products that need to stay maintainable.',
  labels: {
    decision: 'Decision',
    why: 'Why',
    tradeoff: 'Tradeoff',
  },
} as const;

export const ARCHITECTURE_DECISIONS = [
  {
    decision: 'Keep configuration in code, not in the database',
    why: 'Version-controlled config means every environment can be rebuilt the same way, and a change is reviewable before it reaches production instead of being clicked into a live admin UI.',
    tradeoff: 'More deploy discipline up front, but no drift between local, staging, and prod.',
  },
  {
    decision: 'Build for the content editor, not just the launch',
    why: 'Public agencies maintain these sites for years with non-technical staff, so structured fields and defined components hold up better than a freeform editor that decays after handoff.',
    tradeoff: 'Less flexibility on any single page, but the site still looks intentional a year later.',
  },
  {
    decision: 'Audit for accessibility before launch, not after',
    why: 'Public-sector sites have compliance obligations and a wide range of users; catching issues during the build is cheaper than remediating a live site.',
    tradeoff: 'Slower initial delivery, but no separate remediation project later.',
  },
  {
    decision: 'Support the browser matrix the audience actually uses',
    why: 'A county or utility site serves everyone, including people on older hardware and locked-down government machines, so the target is what visitors actually run rather than whatever is current on my own setup.',
    tradeoff: 'More conservative CSS and JavaScript choices, but the site works for the people it was built for.',
  },
] satisfies readonly ArchitectureDecision[];
