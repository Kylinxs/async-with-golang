<div class="tables">
    <h2>{tr}Tables{/tr}</h2>
    <div class="row">
        <div class="col-sm-8 col-md-9">
            <p>{tr}Striped table{/tr}</p>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{tr}Version{/tr}</th>
                        <th>{tr}Star{/tr}</th>
                        <th>{tr}Year{/tr}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>12.x</td>
                        <td>Altair</td>
                        <td>2013</td>
                    </tr>
                    <tr>
                        <td>13.x</td>
                        <td>Fomalhaut</td>
                        <td>2014</td>
                    </tr>
                    <tr>
                        <td>14.x</td>
                        <td>Peony</td>
                        <td>2015</td>
                    </tr>
                    <tr>
                        <td>15.x</td>
                        <td>Situla</td>
                        <td>2016</td>
                    </tr>
                    <tr>
                        <td>16.x</td>
                        <td>Tabby's</td>
                        <td>2016</td>
                    </tr>
                    <tr>
                        <td>17.x</td>
                        <td>Zeta Boötis</td>
                        <td>2017</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-sm-4 col-md-3">
            <div class="input">
                <p class="picker" data-selector=".table-striped > tbody > tr:nth-of-type(odd)" data-element="background-color">
                    <label for="tc-striped-bgcolor-odd">Bg (odd rows):</label>
                    <input id="tc-striped-bgcolor-odd" data-selector=".table-striped > tbody > tr:nth-of-type(odd)" data-element="background-color" data-var="@table-bg-accent" type="text">
                    <span class="input-group-addon"><i></i></span>
                </p>
                <p class="picker" data-selector=".table-striped > tbody > tr:nth-of-type(even)" data-element="background-color">
                    <label for="tc-striped-bgcolor-even">Bg (even rows):</label>
                    <input id="tc-striped-bgcolor-even" data-selector=".table-striped > tbody > tr:nth-of-type(even)" data-element="background-color" type="text">
                    <span class="input-group-addon"><i></i></span>
                </p>
                <p class="picker" data-selector="table tr" data-element="color">
                    <label for="tc-table-color">{tr}Text color:{/tr}</label>
                    <input id="tc-table-color" data-selector="table tr" data-element="color" type="text">
                    <span class="input-group-addon"><i></i></span>
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-8 col-md-9">
            <p>{tr}Bordered table{/tr}</p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="tb">{tr}Site{/tr}</th>
                        <th class="tb">{tr}Name{/tr}</th>
                        <th class="tb">{tr}Purpose{/tr}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="tb"><a href="https://tiki.org/">tiki.org</a></td>
                        <td class="tb">{tr}About &amp; News{/tr}</td>
                        <td class="tb">{tr}Information and introduction portal{/tr}</td>
                    </tr>
                    <tr>
                        <td class="tb"><a href="https://doc.tiki.org/">doc.tiki.org</a></td>
                        <td class="tb">{tr}Documentation{/tr}</td>
                        <td class="tb">{tr}How to use Tiki{/tr}</td>
                    </tr>
                    <tr>
                        <td class="tb"><a href="https://dev.tiki.org/">dev.tiki.org</a></td>
                        <td class="tb">{tr}Development{/tr}</td>
                        <td class="tb">{tr}How make Tiki{/tr}</td>
                    </tr>
                    <tr>
                        <td class="tb"><a href="https://tiki.org/Community">tiki.org/Community</a></td>
                        <td class="tb">{tr}Community{/tr}</td>
                        <td class="tb">{tr}Forums and general community information{/tr}</td>
                    </tr>
                    <tr>
                        <td class="tb"><a href="https://themes.tiki.org/">themes.tiki.org</a></td>
                        <td class="tb">{tr}Themes{/tr}</td>
                        <td class="tb">{tr}How make Tiki look pretty{/tr}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-sm-4 col-md-3">
            <div class="input">
                <p class="picker" data-selector=".table-bordered tr" data-element="background-color">
                    <label for="tc-bordered-bgcolor">{tr}Background:{/tr}</label>
                    <input id="tc-bordered-bgcolor" data-selector=".table-bordered tr" data-element="background-color" data-var="@table-bg" type="text">
                    <span class="input-group-addon"><i></i></span>
                </p>
                <p>
                    <label for="tc-cells-border">{tr}Border:{/tr}</label>
                    <input id="tc-cells-border" class="nocolor" data-selector=".table-bordered .tb" data-element="border" type="text">
                </p>
                <p>
                    <label for="tc-cells-padding">{tr}Padding:{/tr}</label>
                    <input id="tc-cells-padding" class="nocolor" data-selector=".table > tbody > tr > td" data-element="padding" type="text">
                </p>
            </div>
        </div>
    </div>
</div>
