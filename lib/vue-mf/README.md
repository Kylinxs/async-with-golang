
# Recommended IDE: :monocle_face:
[VSCode](https://code.visualstudio.com/)

## VSCode extensions:
1. [Volar](https://marketplace.visualstudio.com/items?itemName=johnsoncodehk.volar)
2. [ESlint](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint)
3. [Prettier](https://marketplace.visualstudio.com/items?itemName=esbenp.prettier-vscode)

All in one: [Volar Extention Pack](https://marketplace.visualstudio.com/items?itemName=MisterJ.vue-volar-extention-pack)

# Installation:
In app folder /lib/vue-mf/(app) run:
```
npm install
npm run watch - development build
npm run build - production build
```

## Application builds are stored in:
```
/storage/public/vue-mf/
```

Note that shared libraries (vue and es-module-shims) are installed as devDependencies of root-config in  /lib/vue-mf/root-config/package.json and copied into 
storage/public/vue-mf/root-config
by /lib/vue-mf/root-config/rollup.config.js

they are made available through and importmap in teamplates/header.tpl

## Global Vue Micro Frontend configuration application dev path:
```
/lib/vue-mf/root-config/
```
