((w, $) => {
    $(document).ready(() => {
        'use strict'

        const root = document.querySelector('.filters-sets')

        if (!root) {
            return;
        }

        root.querySelector('.create-filters-set').addEventListener('click', event => {
            event.preventDefault()
            event.stopPropagation()

            root.querySelector('form').classList.remove('hidden')
        })

        root.querySelectorAll('.share').forEach(element => {
            element.addEventListener('click', event => {
                event.preventDefault()
                event.stopPropagation()

                const request = new XMLHttpRequest()
                request.open('PUT', event.currentTarget.href, true)
                request.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
                request.onreadystatechange = () => {
                    const success = document.createElement('a')
                    success.href = '#'
                    success.textContent = 'Filtre partagé !'

                    element.replaceWith(success)
                }

                request.send()
            })
        })

        root.querySelector('form').addEventListener('submit', event => {
            event.preventDefault()
            event.stopPropagation()

            const form = event.currentTarget
            const nameInput = form.querySelector('input[name="saved_filters[name]"]')
            const adminClassInput = form.querySelector('input[name="saved_filters[adminClass]"]')
            const params = new URLSearchParams(window.location.search || '?filters=reset')

            const request = new XMLHttpRequest()
            request.open('POST', event.currentTarget.action, true)
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
            request.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
            request.onreadystatechange = event => {
                if (XMLHttpRequest.DONE === event.currentTarget.readyState) {
                    form.querySelector('button[type="submit"]').disabled = false

                    const response = JSON.parse(event.currentTarget.response)

                    if (201 === event.currentTarget.status) {
                        form.classList.add('hidden')
                        form.querySelectorAll('.has-error').forEach(element => {
                            element.classList.remove('has-error')
                        })
                        form.querySelectorAll('.help-block').forEach(element => {
                            element.remove()
                        })
                        nameInput.value = ''

                        const link = document.createElement('a')
                        link.href = response.filters
                        link.textContent = response.name

                        const item = document.createElement('li')
                        item.appendChild(link)

                        root.querySelector('.filters-sets-list li:last-child').before(item)
                        root.querySelector('.badge').textContent = (parseInt(root.querySelector('.badge').textContent) + 1).toString()

                        return
                    }

                    if (response.hasOwnProperty('violations')) {
                        const violationsContainers = {}
                        response.violations.forEach(violation => {
                            if (!violation.hasOwnProperty('propertyPath')) {
                                return
                            }

                            if (!violationsContainers.hasOwnProperty(violation.propertyPath)) {
                                const list = document.createElement('ul')
                                const root = document.createElement('div')
                                root.classList.add('help-block')
                                root.appendChild(list)

                                violationsContainers[violation.propertyPath] = {
                                    root: root,
                                    list: list,
                                }
                            }

                            const violationItem = document.createElement('li')
                            violationItem.textContent = violation.title

                            violationsContainers[violation.propertyPath].list.appendChild(violationItem)
                        })

                        Object.entries(violationsContainers).forEach(([name, element]) => {
                            const formGroup = form.querySelector(`[name="saved_filters[${name}]"]`).parentNode
                            formGroup.classList.add('has-error')
                            formGroup.appendChild(element.root)
                        })
                    }
                }
            }

            request.send(`name=${nameInput.value}&adminClass=${adminClassInput.value}&filters=${encodeURIComponent(params.toString())}`)
        })
    })
})(window, jQuery)
