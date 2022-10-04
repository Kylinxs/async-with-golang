//****************************************************************
// Keep this copyright notice:
// This copy of the script is the property of the owner of the
// particular web site you were visiting.
// Do not download the script's files from there.
// For a free download and full instructions go to:
// http://www.treeview.net
//****************************************************************


// Log of changes:
//
//      12 May 03 - Support for Safari Beta 3
//      01 Mar 03 - VERSION 4.3 - Support for checkboxes
//      21 Feb 03 - Added support for Opera 7
//      22 Sep 02 - Added maySelect member for node-by-node control
//                  of selection and highlight
//      21 Sep 02 - Cookie values are now separated by cookieCutter
//      12 Sep 02 - VERSION 4.2 - Can highlight Selected Nodes and
//                  can preserve state through external (DB) IDs
//      29 Aug 02 - Fine tune 'supportDeferral' for IE4 and IE Mac
//      25 Aug 02 - Fixes: STARTALLOPEN, and multi-page frameless
//      09 Aug 02 - Fix repeated folder on Mozilla 1.x
//      31 Jul 02 - VERSION 4.1 - Dramatic speed increase for trees
//      with hundreds or thousands of nodes; changes to the control
//      flags of the gLnk function
//      18 Jul 02 - Changes in pre-load images function
//      13 Jun 02 - Add ICONPATH var to allow for gif subdir
//      20 Apr 02 - Improve support for frame-less layout
//      07 Apr 02 - Minor changes to support server-side dynamic feeding
//                  (example: FavoritesManagerASP)


// Definition of class Folder
// *****************************************************************
function Folder(folderDescription, hreference) //constructor
{
  //constant data
  this.desc = folderDescription;
  this.hreference = hreference;
  this.id = -1;
  this.navObj = 0;
  this.iconImg = 0;
  this.nodeImg = 0;
  this.isLastNode = 0;
  this.iconSrc = ICONPATH + "ftv2folderopen.gif";
  this.iconSrcClosed = ICONPATH + "ftv2folderclosed.gif";
  this.children = new Array;
  this.nChildren = 0;
  this.level = 0;
  this.leftSideCoded = "";
  this.isLastNode=false;
  this.parentObj = null;
  this.maySelect=true;
  this.prependHTML = "";

  //dynamic data
  this.isOpen = false;
  this.isLastOpenedFolder = false;
  this.isRendered = 0;

  //methods
  this.initialize = initializeFolder;
  this.setState = setStateFolder;
  this.addChild = addChild;
  this.createIndex = createEntryIndex;
  this.escondeBlock = escondeBlock;
  this.esconde = escondeFolder;
  this.folderMstr = folderMstr;
  this.renderOb = drawFolder;
  this.totalHeight = totalHeight;
  this.subEntries = folderSubEntries;
  this.linkHTML = linkFolderHTML;
  this.blockStartHTML = blockStartHTML;
  this.blockEndHTML = blockEndHTML;
  this.nodeImageSrc = nodeImageSrc;
  this.iconImageSrc = iconImageSrc;
  this.getID = getID;
  this.forceOpeningOfAncestorFolders = forceOpeningOfAncestorFolders;
}

function initializeFolder(level, lastNode, leftSide)
{
  var j=0;
  var i=0;
  nc = this.nChildren;

  this.createIndex();
  this.level = level;
  this.leftSideCoded = leftSide;

  if (browserVersion == 0 || STARTALLOPEN==1)
    this.isOpen=true;

  if (level>0)
    if (lastNode) //the last child in the children array
        leftSide = leftSide + "0";
    else
        leftSide = leftSide + "1";

  this.isLastNode = lastNode;

  if (nc > 0)
  {
    level = level + 1;
    for (i=0 ; i < this.nChildren; i++)
    {
      if (i == this.nChildren-1)
        this.children[i].initialize(level, 1, leftSide);
      else
        this.children[i].initialize(level, 0, leftSide);
    }
  }
}

function drawFolder(insertAtObj)
{
  var nodeName = "";
  var auxEv = "";
  var docW = "";

  var leftSide = leftSideHTML(this.leftSideCoded);

  if (browserVersion > 0)
    auxEv = "<a class='menu' href='javascript:clickOnNode(\""+this.getID()+"\")'>";
  else
    auxEv = "<a>";

  nodeName = this.nodeImageSrc();

  if (this.level>0)
    if (this.isLastNode) //the last child in the children array
        leftSide = leftSide + "<td valign=top>" + auxEv + "<img name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='" + nodeName + "' width=16 height=22 /></a></td>";
    else
      leftSide = leftSide + "<td valign=top background=" + ICONPATH + "ftv2vertline.gif>" + auxEv + "<img name='nodeIcon" + this.id + "' id='nodeIcon" + this.id + "' src='" + nodeName + "' width=16 height=22 /></a></td>";

  this.isRendered = 1;

  if (browserVersion == 2) {
    if (!doc.yPos)
      doc.yPos=20;
  }

  docW = this.blockStartHTML("folder");

  docW = docW + "<tr>" + leftSide + "<td valign=top>";
  if (USEICONS)
  {
    docW = docW + this.linkHTML(false);
    docW = docW + "<img id='folderIcon" + this.id + "' name='folderIcon" + this.id + "' src='" + this.iconImageSrc() + "' /></a>";
  }
  else
  {
      if (this.prependHTML == "")
        docW = docW + "<img src=" + ICONPATH + "ftv2blank.gif height=2 width=2 />";
  }
  if (WRAPTEXT)
      docW = docW + "</td>"+this.prependHTML+"<td valign=middle width=100%>";
  else
      docW = docW + "</td>"+this.prependHTML+"<td valign=middle nowrap width=100%>";
  if (USETEXTLINKS)
  {
    docW = docW + this.linkHTML(true);
    docW = docW + this.desc + "</a>";
  }
  else
    docW = docW + this.desc;
  docW = docW + "</td>";

  docW = docW + this.blockEndHTML();

  if (insertAtObj == null)
  {
      if (supportsDeferral) {
          doc.write("<div id=domRoot></div>"); //transition between regular flow HTML, and node-insert DOM DHTML
          insertAtObj = getElById("domRoot");
          insertAtObj.insertAdjacentHTML("beforeEnd", docW);
      }
      else
          doc.write(docW);
  }
  else
  {
      insertAtObj.insertAdjacentHTML("afterEnd", docW);
  }

  if (browserVersion == 2)
  {
    this.navObj = doc.layers["folder"+this.id];
    if (USEICONS)
      this.iconImg = this.navObj.document.images["folderIcon"+this.id];
    this.nodeImg = this.navObj.document.images["nodeIcon"+this.id];
    doc.yPos=doc.yPos+this.navObj.clip.height;
  }
  else if (browserVersion != 0)
  {
    this.navObj = getElById("folder"+this.id);
    if (USEICONS)
      this.iconImg = getElById("folderIcon"+this.id);
    this.nodeImg = getElById("nodeIcon"+this.id);
  }
}

function setStateFolder(isOpen)
{
  var subEntries;
  var totalHeight;
  var fIt = 0;
  var i=0;
  var currentOpen;

  if (isOpen == this.isOpen)
    return;

  if (browserVersion == 2)
  {
    totalHeight = 0;
    for (i=0; i < this.nChildren; i++)
      totalHeight = totalHeight + this.children[i].navObj.clip.height;
      subEntries = this.subEntries();
    if (this.isOpen)
      totalHeight = 0 - totalHeight;
    for (fIt = this.id + subEntries + 1; fIt < nEntries; fIt++)
      indexOfEntries[fIt].navObj.moveBy(0, totalHeight);
  }
  this.isOpen = isOpen;

  if (this.getID()!=foldersTree.getID() && PERSERVESTATE && !this.isOpen) //closing
  {
     currentOpen = GetCookie("clickedFolder");
     if (currentOpen != null) {
         currentOpen = currentOpen.replace(this.getID()+cookieCutter, "");
         SetCookie("clickedFolder", currentOpen);
     }
  }

  if (!this.isOpen && this.isLastOpenedfolder)
  {
        lastOpenedFolder = null;
        this.isLastOpenedfolder = false;
  }
  propagateChangesInState(this);
}

function propagateChangesInState(folder)
{
  var i=0;

  //Change icon
  if (folder.nChildren > 0 && folder.level>0)  //otherwise the one given at render stays
    folder.nodeImg.src = folder.nodeImageSrc();

  //Change node
  if (USEICONS)
    folder.iconImg.src = folder.iconImageSrc();

  //Propagate changes
  for (i=folder.nChildren-1; i>=0; i--)
    if (folder.isOpen)
      folder.children[i].folderMstr(folder.navObj);
    else
        folder.children[i].esconde();
}

function escondeFolder()
{
  this.escondeBlock();

  this.setState(0);
}

function linkFolderHTML(isTextLink)
{
  var docW = "";

  if (this.hreference)
  {
    if (USEFRAMES)
      docW = docW + "<a onclick='parent.his[parent.his.length]=\"" + this.desc + "|" +this.hreference +"\"' class='menu' href='" + this.hreference + "' TARGET=\"content\" ";
    else
      docW = docW + "<a class='menu' href='" + this.hreference + "' TARGET=_top ";

    if (isTextLink) {
        docW += "id=\"itemTextLink"+this.id+"\" ";
    }

    if (browserVersion > 0)
      docW = docW + "onclick='javascript:clickOnFolder(\""+this.getID()+"\")'";

    docW = docW + ">";
  }
  else
    docW = docW + "<a>";

  return docW;
}

function addChild(childNode)
{
  this.children[this.nChildren] = childNode;
  childNode.parentObj = this;
  this.nChildren++;
  return childNode;
}

function folderSubEntries()
{
  var i = 0;
  var se = this.nChildren;

  for (i=0; i < this.nChildren; i++){
    if (this.children[i].children) //is a folder
      se = se + this.children[i].subEntries();
  }

  return se;
}

function nodeImageSrc() {
  var srcStr = "";

  if (this.isLastNode) //the last child in the children array
  {
    if (this.nChildren == 0)
      srcStr = ICONPATH + "ftv2lastnode.gif";
    else
      if (this.isOpen)
        srcStr = ICONPATH + "ftv2mlastnode.gif";
      else
        srcStr = ICONPATH + "ftv2plastnode.gif";
  }
  else
  {
    if (this.nChildren == 0)
      srcStr = ICONPATH + "ftv2node.gif";
    else
      if (this.isOpen)
        srcStr = ICONPATH + "ftv2mnode.gif";
      else
        srcStr = ICONPATH +