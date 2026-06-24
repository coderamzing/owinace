import flatpickr from 'flatpickr'

export default function flatpickrTimePickerComponent({
    state,
    minuteIncrement,
    isDisabled,
    placeholder,
}) {
    return {
        picker: null,
        state,

        init() {
            const input = this.$refs.input

            if (! input) {
                return
            }

            const initialValue = this.normalizeTime(this.state)

            this.picker = flatpickr(input, {
                allowInput: true,
                clickOpens: ! isDisabled,
                dateFormat: 'H:i',
                defaultDate: initialValue || null,
                enableTime: true,
                minuteIncrement,
                noCalendar: true,
                time_24hr: true,
                onChange: (_selectedDates, dateStr) => {
                    this.state = dateStr || null
                },
                onClose: (_selectedDates, dateStr) => {
                    this.state = this.normalizeTime(dateStr) || null
                },
            })

            if (isDisabled) {
                input.disabled = true
                this.picker.set('clickOpens', false)
            }

            this.$watch('state', (value) => {
                const normalized = this.normalizeTime(value)

                if (! this.picker) {
                    return
                }

                if (! normalized) {
                    this.picker.clear()

                    return
                }

                if (normalized !== this.picker.input.value) {
                    this.picker.setDate(normalized, false)
                }
            })
        },

        normalizeTime(value) {
            if (! value) {
                return null
            }

            const time = String(value).trim()

            if (/^\d{2}:\d{2}$/.test(time)) {
                return time
            }

            if (/^\d{2}:\d{2}:\d{2}$/.test(time)) {
                return time.slice(0, 5)
            }

            return time
        },

        destroy() {
            this.picker?.destroy()
            this.picker = null
        },
    }
}
