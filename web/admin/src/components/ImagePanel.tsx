import { useMutation } from '@tanstack/react-query'
import { useRef, useState } from 'react'
import { ApiError } from '@gd/api-client'
import { api } from '../api'
import { checkImage, uploadFile } from '../lib/upload'
import { toast } from './Toast'

export interface ImageInfo {
  url: string
}

/** Image of a row that has one: `POST|DELETE ${endpoint}` with the multipart field `image`. Used inside CrudPage forms. */
export function ImagePanel({ endpoint, image, onChange }: { endpoint: string; image: ImageInfo | null | undefined; onChange: (image: ImageInfo | null) => void }) {
  const input = useRef<HTMLInputElement>(null)
  const [current, setCurrent] = useState(image ?? null)
  const [progress, setProgress] = useState<number | null>(null)

  const upload = async (file: File) => {
    const problem = checkImage(file)
    if (problem) return toast.error(problem)

    setProgress(0)
    try {
      const { data } = (await uploadFile(endpoint, file, setProgress)) as { data: { image: ImageInfo | null } }
      setCurrent(data.image)
      onChange(data.image)
      toast.success('Image saved')
    } catch (error) {
      toast.error(error instanceof ApiError && error.status === 422 ? (Object.values(error.details ?? {})[0]?.[0] ?? error.message) : 'Could not upload the image')
    } finally {
      setProgress(null)
      if (input.current) input.current.value = ''
    }
  }

  const remove = useMutation({
    mutationFn: () => api.request('DELETE', endpoint),
    onSuccess: () => {
      setCurrent(null)
      onChange(null)
    },
    onError: () => toast.error('Could not remove the image'),
  })

  return (
    <div className="space-y-2">
      <span className="block text-sm text-ink-muted">Image</span>
      {current && <img src={current.url} alt="" className="max-h-40 rounded-md border border-line" />}
      <div className="flex items-center gap-3">
        <input
          ref={input}
          type="file"
          accept="image/jpeg,image/png,image/webp"
          aria-label="Choose image"
          disabled={progress !== null}
          onChange={(e) => e.target.files?.[0] && void upload(e.target.files[0])}
        />
        {current && progress === null && (
          <button type="button" className="text-danger hover:underline" disabled={remove.isPending} onClick={() => remove.mutate()}>
            Remove
          </button>
        )}
      </div>
      {progress !== null && <progress className="w-full" max={1} value={progress} aria-label="Upload progress" />}
    </div>
  )
}
