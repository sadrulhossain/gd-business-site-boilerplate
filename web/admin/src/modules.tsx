import type { CrudConfig } from './components/CrudPage'
import { ToggleCell } from './components/ToggleCell'

/** "faqs.view" -> row "faqs", column "view". */
export function permissionOption(name: string) {
  const [group = name, ...action] = name.split('.')

  return { value: name, label: action.join('.') || name, group }
}

/** Small preview for a row's `image` ({url} or null). */
const thumb = (r: Record<string, unknown>) => {
  const image = r.image as { url: string } | null | undefined

  return image ? <img src={image.url} alt="" className="h-10 w-14 rounded object-cover" /> : null
}

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
    image: true,
    path: '/services',
    title: 'Services',
    noun: 'Service',
    endpoint: '/v1/services',
    rowLabel: (r) => String(r.title),
    columns: [
      { key: 'image', header: 'Image', render: thumb },
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
    image: true,
    path: '/testimonials',
    title: 'Testimonials',
    noun: 'Testimonial',
    endpoint: '/v1/testimonials',
    rowLabel: (r) => String(r.author_name),
    columns: [
      { key: 'image', header: 'Photo', render: thumb },
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
    image: true,
    path: '/team',
    title: 'Team',
    noun: 'Team member',
    endpoint: '/v1/team',
    rowLabel: (r) => String(r.name),
    columns: [
      { key: 'image', header: 'Photo', render: thumb },
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
    image: true,
    path: '/gallery',
    title: 'Gallery',
    noun: 'Gallery item',
    endpoint: '/v1/gallery',
    rowLabel: (r) => String(r.title ?? r.id),
    columns: [
      { key: 'image', header: 'Image', render: thumb },
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
    // Draft/published is the API's `status`; leave "Publish at" empty to publish immediately.
    key: 'posts',
    path: '/posts',
    title: 'Posts',
    noun: 'Post',
    endpoint: '/v1/posts',
    image: true,
    rowLabel: (r) => String(r.title),
    columns: [
      { key: 'image', header: 'Cover', render: thumb },
      { key: 'title', header: 'Title' },
      { key: 'category', header: 'Category', render: (r) => String((r.category as { name?: string } | null)?.name ?? '') },
      { key: 'status', header: 'Status' },
    ],
    fields: [
      { name: 'title', label: 'Title', bilingual: true, required: true },
      { name: 'slug', label: 'Slug', required: true, hint: 'Lowercase letters, numbers and dashes. Used in the post URL.' },
      { name: 'category_id', label: 'Category', type: 'select', optionsFrom: { endpoint: '/v1/categories', map: (c: { id: number; name: string }) => ({ value: c.id, label: c.name }) } },
      { name: 'status', label: 'Status', type: 'select', initial: 'draft', options: [{ value: 'draft', label: 'Draft' }, { value: 'published', label: 'Published' }] },
      { name: 'published_at', label: 'Publish at', hint: 'Date and time, e.g. 2026-12-01 09:00. Empty publishes immediately.' },
      { name: 'excerpt', label: 'Excerpt', type: 'textarea', bilingual: true },
      { name: 'body', label: 'Body', type: 'richtext', bilingual: true },
      { name: 'seo_title', label: 'SEO title', bilingual: true, hint: 'Up to 70 characters. Falls back to the title.' },
      { name: 'seo_description', label: 'SEO description', type: 'textarea', bilingual: true, hint: 'Up to 320 characters; about 155 shows fully in search results.' },
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
    // Drag a row to reorder; the visibility checkbox saves immediately.
    reorder: { endpoint: '/v1/sections/reorder' },
    rowLabel: (r) => String(r.title),
    columns: [
      { key: 'key', header: 'Key' },
      { key: 'title', header: 'Title' },
      {
        key: 'is_visible',
        header: 'Visible',
        render: (r) => <ToggleCell endpoint="/v1/sections" permissionKey="sections" row={r} field="is_visible" label={`Visible: ${String(r.title)}`} />,
      },
    ],
    fields: [
      { name: 'title', label: 'Title', bilingual: true, required: true },
      { name: 'content', label: 'Content', type: 'textarea', bilingual: true },
      { name: 'sort_order', label: 'Order', type: 'number', initial: 0 },
      { name: 'is_visible', label: 'Visible', type: 'checkbox', initial: false },
    ],
  },
  {
    key: 'users',
    path: '/users',
    title: 'Users',
    noun: 'User',
    endpoint: '/v1/users',
    rowLabel: (r) => String(r.email),
    columns: [
      { key: 'name', header: 'Name' },
      { key: 'email', header: 'Email' },
      { key: 'roles', header: 'Roles', render: (r) => (Array.isArray(r.roles) ? r.roles.join(', ') : '') },
    ],
    fields: [
      { name: 'name', label: 'Name', required: true },
      { name: 'email', label: 'Email', required: true },
      { name: 'password', label: 'Password', type: 'password', hint: 'At least 12 characters. When editing, leave blank to keep the current one.' },
      { name: 'roles', label: 'Roles', type: 'checkgroup', optionsFrom: { endpoint: '/v1/roles', map: (role: { name: string }) => ({ value: role.name, label: role.name }) } },
    ],
  },
  {
    // The matrix: one row per module, one column per action, built from /v1/permissions ("faqs.view" -> row faqs, column view).
    key: 'roles',
    path: '/roles',
    title: 'Roles',
    noun: 'Role',
    endpoint: '/v1/roles',
    rowLabel: (r) => String(r.name),
    columns: [
      { key: 'name', header: 'Name' },
      { key: 'permissions', header: 'Permissions', render: (r) => (Array.isArray(r.permissions) ? r.permissions.length : 0) },
    ],
    fields: [
      { name: 'name', label: 'Name', required: true, hint: 'Lowercase letters, numbers, dashes and underscores.' },
      { name: 'permissions', label: 'Permissions', type: 'checkgroup', optionsFrom: { endpoint: '/v1/permissions', map: permissionOption } },
    ],
  },
  {
    // Seeded tree, edit-only. Rows arrive depth-first; drag reorders within the same parent.
    key: 'menus',
    path: '/menus',
    title: 'Menus',
    noun: 'Menu entry',
    endpoint: '/v1/menus',
    canCreate: false,
    canDelete: false,
    reorder: { endpoint: '/v1/menus/reorder', group: (r) => r.parent_id ?? null },
    rowLabel: (r) => String(r.title),
    columns: [
      { key: 'title', header: 'Title', render: (r) => <span style={{ paddingLeft: `${Number(r.depth ?? 0) * 1.5}rem` }}>{String(r.title)}</span> },
      { key: 'path', header: 'Path' },
      { key: 'permission_name', header: 'Permission' },
    ],
    fields: [
      { name: 'title', label: 'Title', required: true },
      { name: 'icon', label: 'Icon', hint: 'Icon name used by the sidebar.' },
    ],
  },
]
