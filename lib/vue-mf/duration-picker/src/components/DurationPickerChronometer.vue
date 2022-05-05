
<template>
    <div class="dp-chronometer__container">
        <div class="dp-chronometer__group">
            <!-- <span class="dp-chronometer-btn unselectable" v-on:click="addTimer" title="add time">
                <i class="fas fa-plus"></i>
            </span> -->
            <span class="dp-chronometer-btn unselectable" v-if="show" v-on:click="startTimer" title="start">
                <i class="fas fa-play"></i>
            </span>
            <span class="dp-chronometer-btn unselectable" v-if="!show" v-on:click="stopTimer" title="pause">
                <i class="fas fa-pause"></i>
            </span>
            <!-- <span class="dp-chronometer-btn unselectable dp-danger" v-on:click="deleteTimestamp" title="delete">
                <i class="fas fa-trash"></i>
            </span> -->
            <span class="dp-chronometer-btn unselectable dp-danger" v-on:click="resetTimer" title="reset">
                <i class="fas fa-undo-alt"></i>
            </span>
            <!-- <span class="dp-chronometer-btn unselectable" v-on:click="prevTimestamp" title="prev">
                <i class="fas fa-chevron-left"></i>
            </span> -->
            <!-- <span class="dp-chronometer-btn unselectable" v-on:click="nextTimestamp" title="next">
                <i class="fas fa-chevron-right"></i>
            </span> -->
        </div>
    </div>
</template>

<script>
    import { inject } from 'vue';
    import moment from 'moment';
    import momentDurationFormatSetup from 'moment-duration-format';

    momentDurationFormatSetup(moment);
    
    export default {
        name: "DurationPickerChronometer",
        data: function () {
            return {
                store: inject('store'),
                startId: false,
                intervalId: null,
                startDuration: null,
                show: true,
                timestamp: null,
                startTime: null
            };
        },
        mounted: function () {
            if (this.store.state.draft && this.store.state.draft.startTime) {
                this.startTime = this.store.state.draft.startTime;
                this.startTimer();
            }
        },
        unmounted: function () {
            clearInterval(this.intervalId);
        },
        methods: {
            startTimer: function () {
                if (this.startId) return;
                this.store.setPlaying(true);
                this.show = false;
                if (!this.startTime) {
                    this.startTime = moment();
                    this.store.state.inputId && this.store.saveDurationDraft(this.store.state.inputId, { startTime: this.startTime.toISOString() })
                        .then(data => {
                            this.store.state.draft = data;
                        });
                }
                this.timestamp = this.store.getTimestamp(this.store.state.activeTimestamp);
                this.updateActiveTimestamp({startTime: this.startTime});

                // this.startId = requestAnimationFrame(this.startChronometer);
                this.intervalId = setInterval(this.startChronometerInterval, 1000);
            },
            startChronometer: function() {
                const momentCurrentTime = moment();
                const millisecondsDiff = momentCurrentTime.diff(this.startTime);
                this.updateActiveTimestamp({spentTime: moment.duration(millisecondsDiff).add(this.timestamp.spentTime)})
                this.startId = requestAnimationFrame(this.startChronometer);
            },
            startChronometerInterval: function () {
                const momentCurrentTime = moment();
                const millisecondsDiff = momentCurrentTime.diff(this.startTime);
                const currentSpentTime = moment.duration(millisecondsDiff).add(this.timestamp.spentTime);
                this.updateActiveTimestamp({spentTime: currentSpentTime});
            },
            stopTimer: function () {
                this.store.state.inputId && this.store.saveDurationDraft(this.store.state.inputId, { startTime: '', draftAmounts: this.getTotalAmounts() })
                    .then(data => {
                        this.store.state.draft = data;
                    });
                // cancelAnimationFrame(this.startId);
                clearInterval(this.intervalId);
                this.startTime = null;
                this.startId = false;
                this.show = true;
                this.store.setPlaying(false);
                this.updateActiveTimestamp({stopTime: moment()});
            },
            getTotalAmounts: function() {
                const totalDuration = this.store.getTotalDuration();
                const amounts = this.store.__calcDuration(totalDuration);
                return amounts;
            },
            resetTimer: function () {
                if (this.store.state.playing) return;
                if (confirm("Reset to initial state ?")) {
                    // cancelAnimationFrame(this.startId);
                    clearInterval(this.intervalId);
                    this.startId = false;
                    this.show = true;

                    this.store.resetIntitialDuration();
                    this.store.state.inputId && this.store.removeDurationDraft(this.store.state.inputId).then(data => {
                        this.store.state.draft = false;
                    });
                }
            },
            addTimer: function() {
                if (this.store.state.playing) return;
                this.store.setTimestamp('add', {
                    spentTime: moment.duration(0)
                });
            },
            deleteTimestamp: function () {
                if (this.store.state.playing) {
                    confirm(`Can't delete while playing...`)
                    return;
                }
                if (this.store.state.playing || this.store.state.activeTimestamp === 0) {
                    confirm(`Can't delete first timestamp...`)
                    return;
                }
                const timestamp = this.store.getTimestamp(this.store.state.activeTimestamp);
                if (confirm(`Remove timestamp ${timestamp.spentTime.format('h [hrs], m [min], s [sec]')}?`)) {
                    this.store.setTimestamp('delete', timestamp);
                }
            },
            updateActiveTimestamp: function (updatesObj) {
                this.store.setTimestamp('update', {
                    ...this.store.getTimestamp(this.store.state.activeTimestamp),
                    ...updatesObj
                });
            },
            prevTimestamp: function () {
                this.store.setActiveTimestamp(this.store.state.activeTimestamp - 1);
            },
            nextTimestamp: function () {
                this.store.setActiveTimestamp(this.store.state.activeTimestamp + 1);
            }
        }
    };
</script>