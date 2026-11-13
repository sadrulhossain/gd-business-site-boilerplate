/**
 * New order of `ids` after dragging `fromId` onto `toId`: the dragged id takes the target's position.
 * Returns the input unchanged (same reference) when either id is missing or they are the same.
 */
export function moveId<Id>(ids: Id[], fromId: Id, toId: Id): Id[] {
  const from = ids.indexOf(fromId)
  const to = ids.indexOf(toId)
  if (from < 0 || to < 0 || from === to) return ids

  const next = ids.slice()
  next.splice(from, 1)
  next.splice(to, 0, fromId)

  return next
}
