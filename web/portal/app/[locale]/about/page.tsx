import { sectionPage } from '../../../lib/section-page'

const page = sectionPage('about', ['team', 'testimonials'])

export const generateMetadata = page.generateMetadata
export default page.Page
