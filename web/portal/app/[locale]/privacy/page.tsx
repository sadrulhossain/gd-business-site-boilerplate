import { legalPage } from '../../../lib/legal-page'

const page = legalPage('privacy')

export const generateMetadata = page.generateMetadata
export default page.Page
