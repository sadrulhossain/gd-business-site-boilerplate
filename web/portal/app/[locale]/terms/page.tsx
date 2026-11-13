import { legalPage } from '../../../lib/legal-page'

const page = legalPage('terms')

export const generateMetadata = page.generateMetadata
export default page.Page
