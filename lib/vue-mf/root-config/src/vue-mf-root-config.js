import { registerApplication, unregisterApplication, start } from "single-spa";

window.registerApplication = registerApplication;
window.unregisterApplication = unregisterApplication;

start();