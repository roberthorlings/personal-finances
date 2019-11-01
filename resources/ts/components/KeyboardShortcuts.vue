<template>
    <div>
        <slot/>
    </div>
</template>
<script>
    export default {
        name: 'KeyboardShortcuts',
        props: {
            shortcuts: {type: Array, default: []},
        },
        computed: {
            bufferSize() {
                return Math.max(...this.shortcuts.map(s => s.sequence.length));
            }
        },
        data: () => ({
            buffer: [],
        }),
        watch: {
            buffer: function () {
                // Try to match the buffer with the
                this.shortcuts.forEach(shortcut => {
                    if(this.matches(this.buffer, shortcut.sequence)) {
                        shortcut.callback(this.buffer);
                        this.buffer = [];
                    }
                })
            }
        },

        created() {
            window.addEventListener("keypress", this.onKeyPress);
        },

        destroyed() {
            window.removeEventListener("keypress", this.onKeyPress);
        },

        methods: {
            onKeyPress(e) {
                const key = String.fromCharCode(e.keyCode);

                // Don't listen to keypresses in input elements
                const inputElements = ['INPUT', 'TEXTAREA', 'SELECT']
                if (e.target && inputElements.includes(e.target.nodeName))
                    return;

                // Add the key to the buffer, and make sure the buffer is as small as possible
                this.buffer = [...this.buffer, key.toLowerCase()].slice(-this.bufferSize);
            },
            matches(buffer, sequence) {
                if(buffer.length < sequence.length)
                    return false;

                // Use the last part of the buffer for matching
                const relevantBuffer = buffer.slice(-sequence.length);

                // Go over every part in the shortcut. If every part matches
                // we have a match
                for(let i = 0; i < sequence.length; i++) {
                    // An empty character matches anything
                    if(sequence[i] != '' && relevantBuffer[i] !== sequence[i])
                        return false;
                }

                return true;
            }
        },
    }
</script>
