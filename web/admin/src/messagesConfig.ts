/** The contact inbox: read-only list and detail; the only write is marking a message read. */
export interface InboxConfig {
  /** Permission prefix: `${key}.view` to see, `${key}.update` to mark read. */
  key: string
  path: string
  title: string
  endpoint: string
  /** Detail rows, in order. */
  fields: { name: string; label: string }[]
  /** Endpoint that marks one message read. */
  readEndpoint: (id: number) => string
}

export const messages: InboxConfig = {
  key: 'contact',
  path: '/messages',
  title: 'Messages',
  endpoint: '/v1/contact-messages',
  fields: [
    { name: 'name', label: 'Name' },
    { name: 'email', label: 'Email' },
    { name: 'phone', label: 'Phone' },
    { name: 'subject', label: 'Subject' },
    { name: 'message', label: 'Message' },
    { name: 'created_at', label: 'Received' },
  ],
  readEndpoint: (id) => `/v1/contact-messages/${id}/read`,
}

export interface Message {
  id: number
  name: string
  email: string
  phone: string | null
  subject: string | null
  message: string
  read_at: string | null
  created_at: string
}
