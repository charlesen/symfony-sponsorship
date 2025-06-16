import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = []
  static values = {}

  connect() {
    console.log('Contact Picker Controller connected')
  }

  async pickContacts() {
    // Check if the Contact Picker API is available
    if (!('contacts' in navigator && 'ContactsManager' in window)) {
      this.showError("L'API Contact Picker n'est pas disponible sur ce navigateur.")
      return
    }

    try {
      // Check if the browser supports the required properties
      const supportedProperties = await navigator.contacts.getProperties()
      const requiredProperties = ['name', 'email']
      const hasRequiredProperties = requiredProperties.every(prop => 
        supportedProperties.includes(prop)
      )

      if (!hasRequiredProperties) {
        throw new Error('Required contact properties not supported')
      }

      // Open the contact picker
      const contacts = await navigator.contacts.select(['name', 'email'], { multiple: true })

      if (!contacts || contacts.length === 0) {
        return // User cancelled the contact picker
      }

      // Format contacts for the LiveComponent
      const formattedContacts = contacts
        .filter(contact => {
          // Only include contacts with at least an email
          const hasEmail = contact.email && contact.email.length > 0
          if (!hasEmail) {
            console.warn('Contact skipped: No email address found')
          }
          return hasEmail
        })
        .map(contact => ({
          firstName: this.getBestName(contact.name) || '',
          email: contact.email[0] // We already filtered out empty emails
        }))

      if (formattedContacts.length === 0) {
        this.showError('Aucun contact avec une adresse email valide trouvé.')
        return
      }

      // Dispatch event to update the LiveComponent
      this.dispatch('contacts-selected', { 
        detail: { contacts: formattedContacts },
        prefix: 'contact-picker'
      })

    } catch (error) {
      console.error('Error selecting contacts:', error)
      this.showError("Impossible d'accéder aux contacts. Vérifiez les permissions de votre navigateur.")
    }
  }

  handleContacts(event) {
    // This method will be called when the contacts-selected event is triggered
    const { contacts } = event.detail
    
    // Dispatch the event to update the LiveComponent
    this.dispatch('addContacts', {
      detail: { contacts },
      prefix: 'live',
      target: this.element.closest('[data-controller~="live"]')
    })
  }

  getBestName(names) {
    if (!names || names.length === 0) return ''
    
    // Return the first non-empty name
    return names.find(name => name && name.trim() !== '') || ''
  }

  showError(message) {
    // Dispatch an error event that can be handled by the LiveComponent
    this.dispatch('error', { 
      detail: { message },
      prefix: 'contact-picker'
    })
    
    // Also show a browser alert as fallback
    alert(message)
  }
}
