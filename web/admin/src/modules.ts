import type { CrudConfig } from './components/CrudPage'

const yesNo = (value: unknown) => (value ? 'Yes' : 'No')

/**
 * Admin modules are data. Each entry becomes a route (`path`, gated by `${key}.view`) and a CrudPage.
 * Sidebar entries come from the API (/me/menus), so a module also needs its menu row seeded.
 */
export const modules: CrudConfig[] = [
  {
    key: 'faqs',
    path: '/faqs',
    title: 'FAQs',
    noun: 'FAQ',
    endpoint: '/v1/faqs',
    rowLabel: (r) => String(r.question),
    columns: [
      { key: 'question', header: 'Question' },
      { key: 'sort_order', header: 'Order' },
      { key: 'is_visible', header: 'Visible', render: (r) => yesNo(r.is_visible) },
    ],
    fields: [
      { name: 'question', label: 'Question', bilingual: true, required: true },
      { name: 'answer', label: 'Answer', type: 'textarea', bilingual: true, required: true },
      { name: 'sort_order', label: 'Order', type: 'number', initial: 0 },
      { name: 'is_visible', label: 'Visible', type: 'checkbox', initial: true },
    ],
  },
  {
    key: 'services',
    path: '/services',
    title: 'Services',
    noun: 'Service',
    endpoint: '/v1/services',
    rowLabel: (r) => String(r.title),
    columns: [
      { key: 'title', header: 'Title' },
      { key: 'sort_order', header: 'Order' },
      { key: 'is_visible', header: 'Visible', render: (r) => yesNo(r.is_visible) },
    ],
    fields: [
      { name: 'title', label: 'Title', bilingual: true, required: true },
      { name: 'description', label: 'Description', type: 'textarea', bilingual: true },
      { name: 'sort_order', label: 'Order', type: 'number', initial: 0 },
      { name: 'is_visible', label: 'Visible', type: 'checkbox', initial: true },
    ],
  },
  {
    key: 'testimonials',
    path: '/testimonials',
    title: 'Testimonials',
    noun: 'Testimonial',
    endpoint: '/v1/testimonials',
    rowLabel: (r) => String(r.author_name),
    columns: [
      { key: 'author_name', header: 'Author' },
      { key: 'quote', header: 'Quote' },
      { key: 'is_visible', header: 'Visible', render: (r) => yesNo(r.is_visible) },
    ],
    fields: [
      { name: 'author_name', label: 'Author', required: true },
      { name: 'author_role', label: 'Role', bilingual: true },
      { name: 'quote', label: 'Quote', type: 'textarea', bilingual: true, required: true },
      { name: 'sort_order', label: 'Order', type: 'number', initial: 0 },
      { name: 'is_visible', label: 'Visible', type: 'checkbox', initial: true },
    ],
  },
  {
    key: 'team',
    path: '/team',
    title: 'Team',
    noun: 'Team member',
    endpoint: '/v1/team',
    rowLabel: (r) => String(r.name),
    columns: [
      { key: 'name', header: 'Name' },
      { key: 'role', header: 'Role' },
      { key: 'is_visible', header: 'Visible', render: (r) => yesNo(r.is_visible) },
    ],
    fields: [
      { name: 'name', label: 'Name', required: true },
      { name: 'role', label: 'Role', bilingual: true, required: true },
      { name: 'bio', label: 'Bio', type: 'textarea', bilingual: true },
      { name: 'sort_order', label: 'Order', type: 'number', initial: 0 },
      { name: 'is_visible', label: 'Visible', type: 'checkbox', initial: true },
    ],
  },
  {
    key: 'gallery',
    path: '/gallery',
    title: 'Gallery',
    noun: 'Gallery item',
    endpoint: '/v1/gallery',
    rowLabel: (r) => String(r.title ?? r.id),
    columns: [
      { key: 'title', header: 'Title' },
      { key: 'caption', header: 'Caption' },
      { key: 'is_visible', header: 'Visible', render: (r) => yesNo(r.is_visible) },
    ],
    fields: [
      { name: 'title', label: 'Title', bilingual: true },
      { name: 'caption', label: 'Caption', bilingual: true },
      { name: 'sort_order', label: 'Order', type: 'number', initial: 0 },
      { name: 'is_visible', label: 'Visible', type: 'checkbox', initial: true },
    ],
  },
  {
    key: 'categories',
    path: '/categories',
    title: 'Categories',
    noun: 'Category',
    endpoint: '/v1/categories',
    rowLabel: (r) => String(r.name),
    columns: [
      { key: 'name', header: 'Name' },
      { key: 'slug', header: 'Slug' },
      { key: 'sort_order', header: 'Order' },
    ],
    fields: [
      { name: 'name', label: 'Name', bilingual: true, required: true },
      { name: 'slug', label: 'Slug', required: true, hint: 'Lowercase letters, numbers and dashes.' },
      { name: 'sort_order', label: 'Order', type: 'number', initial: 0 },
    ],
  },
  {
    // Seeded and edit-only: the portal renders sections by key.
    key: 'sections',
    path: '/sections',
    title: 'Sections',
    noun: 'Section',
    endpoint: '/v1/sections',
    canCreate: false,
    canDelete: false,
    rowLabel: (r) => String(r.title),
    columns: [
      { key: 'key', header: 'Key' },
      { key: 'title', header: 'Title' },
      { key: 'is_visible', header: 'Visible', render: (r) => yesNo(r.is_visible) },
    ],
    fields: [
      { name: 'title', label: 'Title', bilingual: true, required: true },
      { name: 'content', label: 'Content', type: 'textarea', bilingual: true },
      { name: 'sort_order', label: 'Order', type: 'number', initial: 0 },
      { name: 'is_visible', label: 'Visible', type: 'checkbox', initial: false },
    ],
  },
]
