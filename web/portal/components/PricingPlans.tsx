import type { Pricing, Settings } from '../lib/api'
import type { Messages } from '../lib/i18n'
import { featureList, formatPrice } from '../lib/money'

function Check() {
  return (
    <svg viewBox="0 0 24 24" aria-hidden="true" className="mt-1 size-4 shrink-0 text-brand-600" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
      <path d="M20 6 9 17l-5-5" />
    </svg>
  )
}

/** A row of plan cards for one service. The featured plan is highlighted. */
export function PricingPlans({ plans, settings, locale, messages }: { plans: Pricing[]; settings: Settings; locale: string; messages: Messages }) {
  return (
    <ul className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      {plans.map((plan) => (
        <li
          key={plan.id}
          className={`relative flex flex-col rounded-lg border bg-surface p-6 shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift ${plan.is_featured ? 'border-brand-500 ring-2 ring-brand-500/30' : 'border-line'}`}
        >
          {plan.is_featured && (
            <span className="absolute -top-3 start-6 rounded-full bg-brand-600 px-3 py-1 text-xs font-semibold tracking-wide text-white uppercase">{messages.pricing.featured}</span>
          )}
          <h3 className="text-xl font-bold tracking-tight">{plan.name}</h3>
          {plan.description && <p className="mt-1 text-ink-muted">{plan.description}</p>}
          <p className="mt-5 flex flex-wrap items-baseline gap-x-2">
            <span className="font-display text-4xl font-extrabold tracking-tight text-brand-700">{formatPrice(plan.price, settings, locale)}</span>
            {plan.unit && <span className="text-ink-muted">{plan.unit}</span>}
          </p>
          <ul className="mt-5 space-y-2.5 border-t border-line pt-5">
            {featureList(plan.features).map((feature) => (
              <li key={feature} className="flex gap-2.5">
                <Check />
                <span>{feature}</span>
              </li>
            ))}
          </ul>
        </li>
      ))}
    </ul>
  )
}
