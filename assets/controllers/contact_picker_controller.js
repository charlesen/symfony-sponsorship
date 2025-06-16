import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ["errorContainer"]
  static values = {
    targetComponent: String
  }

  connect() {
    console.log('Contact Picker Controller connected')
  }

  async pickContacts() {
    if (!('contacts' in navigator && 'ContactsManager' in window)) {
      this.showError("L'API Contact Picker n'est pas disponible sur ce navigateur.")
      return
    }

    try {
      const supportedProperties = await navigator.contacts.getProperties()
      const requiredProperties = ['name', 'email']
      const hasRequiredProperties = requiredProperties.every(prop => supportedProperties.includes(prop))

      if (!hasRequiredProperties) {
        throw new Error('Les propriétés nécessaires ne sont pas supportées')
      }

      const contacts = await navigator.contacts.select(['name', 'email'], { multiple: true })

      if (!contacts || contacts.length === 0) return

      const formattedContacts = contacts
        .filter(c => c.email?.length)
        .map(c => ({
          firstName: this.getBestName(c.name),
          email: c.email[0]
        }))

      if (formattedContacts.length === 0) {
        this.showError('Aucun contact valide avec e-mail.')
        return
      }

      this.dispatch('contacts-selected', {
        detail: { contacts: formattedContacts },
        prefix: 'contact-picker'
      })

      this.showInfo(`${formattedContacts.length} contact(s) ajouté(s).`)

    } catch (error) {
      console.error('Erreur Contact Picker :', error)
      this.showError("Impossible d'accéder aux contacts. Vérifiez les permissions du navigateur.")
    }
  }

  handleContacts(event) {
    const { contacts } = event.detail

    this.dispatch('addContacts', {
      detail: { contacts },
      prefix: 'live',
      target: this.element.closest('[data-controller~="live"]')
    })
  }

  getBestName(names) {
    if (!names || names.length === 0) return ''
    return names.find(name => name && name.trim() !== '') || ''
  }

  showError(message) {
    this.renderMessage(message, 'bg-red-100 text-red-800')
  }

  showInfo(message) {
    this.renderMessage(message, 'bg-green-100 text-green-800')
  }

  renderMessage(message, classes) {
    if (!this.hasErrorContainerTarget) return alert(message)

    const el = this.errorContainerTarget
    el.textContent = message
    el.className = `mt-2 p-2 rounded ${classes}`
    el.classList.remove('hidden')
    setTimeout(() => el.classList.add('hidden'), 5000)
  }
}
