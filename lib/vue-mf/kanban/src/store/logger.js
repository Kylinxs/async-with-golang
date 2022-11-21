import { createLogger } from 'vuex';

// If you are using vue-devtools you probably don't need this.
export default createLogger({
    collapsed: false, // auto-expand logged mutations
    filter(mutation, stateBefore, stateAfter) {
        // returns `true` if a mutation should be logged
        // `mutation` is a `{ type, payload }`
        return mutation.type !== "aBlocklistedMutation"
    },
    actionFilter(action, state) {
        // same as `filter` but for actions
        // `action` is a `{ type, payload }`
        return action.type !== "aBlocklistedAction"
    },
    transformer(state) {
        // transform the state before logging it.
        // for example return only a specific sub-tree
        return state
    },
    mutationTransformer(mutation) {
        // mutations are logged in the format of `{ type, payload }`
        // we can format it any way we want.
        return mutation.type
    },
    actionTransformer(action) {
        // Same as mutationTransformer but for actions
        return action.type
    },
    logActions: true, // Log Actions
    logMutations: true, // Log mutations
    logger: console, // implementation of the `console` API, default `console`
})
