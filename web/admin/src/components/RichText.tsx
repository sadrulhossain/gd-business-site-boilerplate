import { EditorContent, useEditor } from '@tiptap/react'
import StarterKit from '@tiptap/starter-kit'
import { useEffect } from 'react'

/**
 * Rich-text editor (TipTap). Value in and out is HTML. The extension set is limited to what the API's HtmlSanitizer
 * allows (headings 2-3, lists, quote, code, links, rules), so what you see is what is stored; the API sanitises regardless.
 */
export function RichText({ id, label, value, onChange }: { id: string; label: string; value: string; onChange: (html: string) => void }) {
  const editor = useEditor({
    extensions: [
      StarterKit.configure({ heading: { levels: [2, 3] }, link: { openOnClick: false, autolink: false } }),
    ],
    content: value,
    editorProps: { attributes: { id, role: 'textbox', 'aria-multiline': 'true', 'aria-label': label, class: 'rich-text min-h-40 px-2.5 py-2 focus:outline-none' } },
    // An empty document is '' (not '<p></p>'), so optional fields stay blank.
    onUpdate: ({ editor: e }) => onChange(e.isEmpty ? '' : e.getHTML()),
  })

  // External changes (a different row loaded) replace the content; our own edits already match and are skipped.
  useEffect(() => {
    if (editor && value !== (editor.isEmpty ? '' : editor.getHTML())) editor.commands.setContent(value, { emitUpdate: false })
  }, [editor, value])

  if (!editor) return null

  const button = (name: string, active: boolean, run: () => void, text: string) => (
    <button
      key={name}
      type="button"
      aria-label={name}
      aria-pressed={active}
      className={`rounded px-2 py-1 text-sm ${active ? 'bg-primary-600 text-white' : 'hover:bg-primary-50'}`}
      onClick={run}
    >
      {text}
    </button>
  )
  const chain = () => editor.chain().focus()

  const setLink = () => {
    const previous = (editor.getAttributes('link').href as string | undefined) ?? ''
    const url = window.prompt('Link URL (https://, mailto: or tel:)', previous)
    if (url === null) return
    if (url === '') return void chain().extendMarkRange('link').unsetLink().run()
    if (!/^(https?:\/\/|mailto:|tel:)/i.test(url)) return
    chain().extendMarkRange('link').setLink({ href: url }).run()
  }

  return (
    <div className="rounded-md border border-line bg-surface focus-within:outline-2 focus-within:outline-primary-500">
      <div role="toolbar" aria-label={`${label} formatting`} className="flex flex-wrap gap-1 border-b border-line p-1">
        {button('Bold', editor.isActive('bold'), () => chain().toggleBold().run(), 'B')}
        {button('Italic', editor.isActive('italic'), () => chain().toggleItalic().run(), 'I')}
        {button('Strikethrough', editor.isActive('strike'), () => chain().toggleStrike().run(), 'S')}
        {button('Heading 2', editor.isActive('heading', { level: 2 }), () => chain().toggleHeading({ level: 2 }).run(), 'H2')}
        {button('Heading 3', editor.isActive('heading', { level: 3 }), () => chain().toggleHeading({ level: 3 }).run(), 'H3')}
        {button('Bullet list', editor.isActive('bulletList'), () => chain().toggleBulletList().run(), '• List')}
        {button('Numbered list', editor.isActive('orderedList'), () => chain().toggleOrderedList().run(), '1. List')}
        {button('Quote', editor.isActive('blockquote'), () => chain().toggleBlockquote().run(), '“ ”')}
        {button('Code block', editor.isActive('codeBlock'), () => chain().toggleCodeBlock().run(), '</>')}
        {button('Link', editor.isActive('link'), setLink, 'Link')}
        {button('Undo', false, () => chain().undo().run(), '↶')}
        {button('Redo', false, () => chain().redo().run(), '↷')}
      </div>
      <EditorContent editor={editor} />
    </div>
  )
}
