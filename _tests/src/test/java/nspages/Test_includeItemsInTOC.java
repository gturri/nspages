package nspages;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

import java.util.ArrayList;
import java.util.List;

import static org.junit.Assert.assertEquals;

public class Test_includeItemsInTOC extends Helper {
    private static final String ns = "ordre_alphabetique_ns";

    @Test
    public void withoutOption(){
        generatePage(ns + ":start", addTitlesInOrderToHaveAToc("<nspages -subns>"));

        // Assert links (or their surrounding span) do not have any html id
        List<InternalLink> expectedLinks = new ArrayList<>();
        expectedLinks.add(new InternalLink(ns + ":a:start", "a", ""));
        expectedLinks.add(new InternalLink(ns + ":aa:start", "aa", ""));
        expectedLinks.add(new InternalLink(ns + ":b:start", "b", ""));
        expectedLinks.add(new InternalLink(ns + ":start", "start", ""));
        assertSameLinks(expectedLinks);

        // Assert the TOC only has links for the normal headers
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        assertSameTOC(expectedTOCLinks);

    }

    @Test
    public void withOption(){
        generatePage(ns + ":start", addTitlesInOrderToHaveAToc("<nspages -subns -includeItemsInTOC>"));

        // Assert links (or their surrounding span) have the expected html id
        List<InternalLink> expectedLinks = new ArrayList<>();
        expectedLinks.add(new InternalLink(ns + ":a:start", "a", "nspages_" + ns + "astart"));
        expectedLinks.add(new InternalLink(ns + ":aa:start", "aa", "nspages_" + ns + "aastart"));
        expectedLinks.add(new InternalLink(ns + ":b:start", "b", "nspages_" + ns + "bstart"));
        expectedLinks.add(new InternalLink(ns + ":start", "start", "nspages_" + ns + "start"));
        assertSameLinks(expectedLinks);

        // Assert the TOC has the expected links
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        expectedTOCLinks.addAll(expectedNsPagesTOCLinks());
        assertSameTOC(expectedTOCLinks);
    }

    @Test
    public void withOptionAndh1(){
        generatePage(ns + ":start", addTitlesInOrderToHaveAToc("<nspages -h1 -subns -includeItemsInTOC>"));

        // Assert links (or their surrounding span) have the expected html id
        List<InternalLink> expectedLinks = new ArrayList<>();
        expectedLinks.add(new InternalLink(ns + ":aa:start", "aa", "nspages_" + ns + "aastart"));
        expectedLinks.add(new InternalLink(ns + ":b:start", "Y", "nspages_" + ns + "bstart"));
        expectedLinks.add(new InternalLink(ns + ":a:start", "Z", "nspages_" + ns + "astart"));
        expectedLinks.add(new InternalLink(ns + ":start", "A", "nspages_" + ns + "start"));
        assertSameLinks(expectedLinks);

        // Assert the TOC has the expected links
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        expectedTOCLinks.add(new TOCLink(2, getDriver().getCurrentUrl() + "#nspages_" + ns + "aastart", "aa"));
        expectedTOCLinks.add(new TOCLink(2 ,getDriver().getCurrentUrl() + "#nspages_" + ns + "bstart", "Y"));
        expectedTOCLinks.add(new TOCLink(2, getDriver().getCurrentUrl() + "#nspages_" + ns + "astart", "Z"));
        expectedTOCLinks.add(new TOCLink(2 ,getDriver().getCurrentUrl() + "#nspages_" + ns + "start", "A"));
        assertSameTOC(expectedTOCLinks);

    }

    @Test
    public void withAfterAH2Title(){
        generatePage(ns + ":start", addTitlesInOrderToHaveAToc("=====D=====\n<nspages -subns -includeItemsInTOC>"));

        // Assert links (or their surrounding span) have the expected html id
        List<InternalLink> expectedLinks = new ArrayList<>();
        expectedLinks.add(new InternalLink(ns + ":a:start", "a", "nspages_" + ns + "astart"));
        expectedLinks.add(new InternalLink(ns + ":aa:start", "aa", "nspages_" + ns + "aastart"));
        expectedLinks.add(new InternalLink(ns + ":b:start", "b", "nspages_" + ns + "bstart"));
        expectedLinks.add(new InternalLink(ns + ":start", "start", "nspages_" + ns + "start"));
        assertSameLinks(expectedLinks);

        // Assert the TOC has the expected links
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        expectedTOCLinks.add(new TOCLink(2 ,getDriver().getCurrentUrl() + "#d", "D"));
        expectedTOCLinks.addAll(expectedNsPagesTOCLinks(2));
        assertSameTOC(expectedTOCLinks);
    }

    @Test
    public void withTwoNspagesTagsIdsAreStillUnique(){
        generatePage(ns + ":start", addTitlesInOrderToHaveAToc("<nspages -subns -includeItemsInTOC><nspages -subns -includeItemsInTOC>"));

        // Assert links (or their surrounding span) have the expected html id
        List<InternalLink> expectedLinks = new ArrayList<>();
        //  Links for the 1st tag
        expectedLinks.add(new InternalLink(ns + ":a:start", "a", "nspages_" + ns + "astart"));
        expectedLinks.add(new InternalLink(ns + ":aa:start", "aa", "nspages_" + ns + "aastart"));
        expectedLinks.add(new InternalLink(ns + ":b:start", "b", "nspages_" + ns + "bstart"));
        expectedLinks.add(new InternalLink(ns + ":start", "start", "nspages_" + ns + "start"));
        //  Links for the 2nd tag: same links but with unique ids
        expectedLinks.add(new InternalLink(ns + ":a:start", "a", "nspages_" + ns + "astart1"));
        expectedLinks.add(new InternalLink(ns + ":aa:start", "aa", "nspages_" + ns + "aastart1"));
        expectedLinks.add(new InternalLink(ns + ":b:start", "b", "nspages_" + ns + "bstart1"));
        expectedLinks.add(new InternalLink(ns + ":start", "start", "nspages_" + ns + "start1"));
        assertSameLinks(expectedLinks);

        // Assert the TOC has links for both nspages tags and with correct id
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        expectedTOCLinks.addAll(expectedNsPagesTOCLinks());
        expectedTOCLinks.addAll(expectedNsPagesTOCLinks("1"));
        assertSameTOC(expectedTOCLinks);
    }

    /**
     * the -tree printer is a bit more subtle as the others because the indentation of the links in the
     * TOC should reflect the level of the entry in the tree
     */
    @Test
    public void withTreeButNotTheOption(){
        String treeRoot = "trees:standard_tree";
        generatePage(treeRoot + ":page_at_root_level", addTitlesInOrderToHaveAToc("<nspages -r=3 -tree -subns -nopages -pagesInNs>"));

        // Assert links (or their surrounding span) don't have a html id
        List<InternalLink> expectedLinks = new ArrayList<>();
        expectedLinks.add(new InternalLink(treeRoot + ":section1:start", "section1", ""));
        expectedLinks.add(new InternalLink(treeRoot + ":section1:subsection1:start", "subsection1", ""));
        expectedLinks.add(new InternalLink(treeRoot + ":section1:subsection2:start", "subsection2", ""));
        expectedLinks.add(new InternalLink(treeRoot + ":section1:subsection3:start", "subsection3", ""));
        expectedLinks.add(new InternalLink(treeRoot + ":section2:start", "section2", ""));
        expectedLinks.add(new InternalLink(treeRoot + ":section3:start", "section3", ""));
        WebElement nspagesRoot = getDriver().findElement(By.className("plugin_nspages"));
        assertSameLinks(expectedLinks, nspagesRoot.findElements(By.tagName("a")));

        // Assert the TOC doesn't have links to nspages items
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        assertSameTOC(expectedTOCLinks);
    }
    @Test
    public void withTreeAndTheOption(){
        String treeRoot = "trees:standard_tree";
        generatePage(treeRoot + ":page_at_root_level", addTitlesInOrderToHaveAToc("<nspages -r=3 -tree -subns -nopages -pagesInNs -includeItemsInTOC>"));

        // Assert links (or their surrounding span) have the expected html id
        List<InternalLink> expectedLinks = new ArrayList<>();
        expectedLinks.add(new InternalLink(treeRoot + ":section1:start", "section1", "nspages_treesstandard_treesection1"));
        expectedLinks.add(new InternalLink(treeRoot + ":section1:subsection1:start", "subsection1", "nspages_treesstandard_treesection1subsection1start"));
        expectedLinks.add(new InternalLink(treeRoot + ":section1:subsection2:start", "subsection2", "nspages_treesstandard_treesection1subsection2start"));
        expectedLinks.add(new InternalLink(treeRoot + ":section1:subsection3:start", "subsection3", "nspages_treesstandard_treesection1subsection3"));
        expectedLinks.add(new InternalLink(treeRoot + ":section2:start", "section2", "nspages_treesstandard_treesection2start"));
        expectedLinks.add(new InternalLink(treeRoot + ":section3:start", "section3", "nspages_treesstandard_treesection3start"));
        WebElement nspagesRoot = getDriver().findElement(By.className("plugin_nspages"));
        assertSameLinks(expectedLinks, nspagesRoot.findElements(By.tagName("a")));

        // Assert the TOC has the links with the correct indentation level
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        expectedTOCLinks.add(new TOCLink(2, getDriver().getCurrentUrl() + "#nspages_treesstandard_treesection1", "section1"));
        expectedTOCLinks.add(new TOCLink(3, getDriver().getCurrentUrl() + "#nspages_treesstandard_treesection1subsection1start", "subsection1"));
        expectedTOCLinks.add(new TOCLink(3, getDriver().getCurrentUrl() + "#nspages_treesstandard_treesection1subsection2start", "subsection2"));
        expectedTOCLinks.add(new TOCLink(3, getDriver().getCurrentUrl() + "#nspages_treesstandard_treesection1subsection3", "subsection3"));
        expectedTOCLinks.add(new TOCLink(2, getDriver().getCurrentUrl() + "#nspages_treesstandard_treesection2start", "section2"));
        expectedTOCLinks.add(new TOCLink(2, getDriver().getCurrentUrl() + "#nspages_treesstandard_treesection3start", "section3"));
        assertSameTOC(expectedTOCLinks);
    }

    /**
     * the -usePictures printer doesn't use the same code as the other in order to implement this feature
     * hence those ad hoc tests
     */
    @Test
    public void withPicturesButNotTheOption(){
        generatePage(ns + ":start", addTitlesInOrderToHaveAToc("<nspages -subns -nopages -usePictures>"));

        // Assert links don't have any html id
        List<String> expectedIds = new ArrayList<>();
        expectedIds.add("");
        expectedIds.add("");
        expectedIds.add("");
        List<WebElement> actualPictures = getPictureLinks();
        assertEquals(expectedIds.size(), actualPictures.size());
        for(int idx=0 ; idx < expectedIds.size() ; idx++){
            assertEquals(expectedIds.get(idx), getHtmlId(actualPictures.get(idx)));
        }

        // Assert the TOC doesn't have nspages
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        assertSameTOC(expectedTOCLinks);
    }

    @Test
    public void withPicturesAndOption(){
        generatePage(ns + ":start", addTitlesInOrderToHaveAToc("<nspages -subns -nopages -includeItemsInTOC -usePictures>"));

        // Assert links have the expected html id
        List<String> expectedIds = new ArrayList<>();
        expectedIds.add("nspages_" + ns + "astart");
        expectedIds.add("nspages_" + ns + "aastart");
        expectedIds.add("nspages_" + ns + "bstart");
        List<WebElement> actualPictures = getPictureLinks();
        assertEquals(expectedIds.size(), actualPictures.size());
        for(int idx=0 ; idx < expectedIds.size() ; idx++){
            assertEquals(expectedIds.get(idx), getHtmlId(actualPictures.get(idx)));
        }

        // Assert the TOC has the expected links
        List<TOCLink> expectedTOCLinks = expectedFirstTOCLinks();
        expectedTOCLinks.addAll(expectedNsPagesTOCLinksForNsOnly());
        assertSameTOC(expectedTOCLinks);
    }

    private void assertSameTOC(List<TOCLink> expectedLinks){
        List<TOCLink> actualLinks = getActualTocLinks();

        assertEquals(expectedLinks.size(), actualLinks.size());
        for(int numLink = 0 ; numLink < expectedLinks.size() ; numLink++ ){
            assertEquals(expectedLinks.get(numLink), actualLinks.get(numLink));
        }
    }

    private List<TOCLink> getActualTocLinks(){
        WebElement tocRoot = getDriver().findElement(By.id("dw__toc"));
        return getTocLevelLinks(tocRoot, 1);
    }

    private List<TOCLink> getTocLevelLinks(WebElement currentRoot, int nextLevel){
        List<TOCLink> tocLevelLinks = new ArrayList<>();
        for(WebElement nextLevelItem : currentRoot.findElements(By.className("level" + nextLevel))){
            WebElement link = nextLevelItem.findElement(By.xpath("./div/a"));
            tocLevelLinks.add(new TOCLink(nextLevel, link.getAttribute("href"), link.getAttribute("innerHTML")));
            tocLevelLinks.addAll(getTocLevelLinks(nextLevelItem, nextLevel+1));
        }
        return tocLevelLinks;
    }

    static class TOCLink {
        private final int level;
        private final String target;
        private final String text;

        public TOCLink(int level, String target, String text){
            this.level = level;
            this.target = target;
            this.text = text;
        }

        @Override
        public boolean equals(Object obj) {
            if (obj == null) {return false;}
            if (obj.getClass() != this.getClass()) {return false;}
            final TOCLink other = (TOCLink) obj;
            return other.level == level && other.target.equals(target) && other.text.equals(text);
        }

        @Override
        public String toString(){return "TOCLink[" + level + ", " + target + ", " + text + "]";}
    }

    private static String addTitlesInOrderToHaveAToc(String pageContent){
        return "======A======\n"
                + "======B======\n"
                + "======C======\n"
                + pageContent;
    }

    private List<TOCLink> expectedFirstTOCLinks(){
        List<TOCLink> expectedFirstTOCLinks = new ArrayList<>();
        expectedFirstTOCLinks.add(new TOCLink(1, getDriver().getCurrentUrl() + "#a", "A"));
        expectedFirstTOCLinks.add(new TOCLink(1, getDriver().getCurrentUrl() + "#b", "B"));
        expectedFirstTOCLinks.add(new TOCLink(1 ,getDriver().getCurrentUrl() + "#c", "C"));
        return expectedFirstTOCLinks;
    }

    private List<TOCLink> expectedNsPagesTOCLinks(){
        return expectedNsPagesTOCLinks("", 1, true);
    }

    private List<TOCLink> expectedNsPagesTOCLinks(int lastTitleLevel){
        return expectedNsPagesTOCLinks("", lastTitleLevel, true);
    }

    private List<TOCLink> expectedNsPagesTOCLinks(String dedupIdPrefix){
        return expectedNsPagesTOCLinks(dedupIdPrefix, 1, true);
    }

    private List<TOCLink> expectedNsPagesTOCLinksForNsOnly(){
        return expectedNsPagesTOCLinks("", 1, false);
    }

    private List<TOCLink> expectedNsPagesTOCLinks(String dedupIdPrefix, int lastTitleLevel, boolean withPages){
        List<TOCLink> expectedTOCLinks = new ArrayList<>();
        expectedTOCLinks.add(new TOCLink(1 + lastTitleLevel, getDriver().getCurrentUrl() + "#nspages_" + ns + "astart" + dedupIdPrefix, "a"));
        expectedTOCLinks.add(new TOCLink(1 + lastTitleLevel, getDriver().getCurrentUrl() + "#nspages_" + ns + "aastart" + dedupIdPrefix, "aa"));
        expectedTOCLinks.add(new TOCLink(1 + lastTitleLevel,getDriver().getCurrentUrl() + "#nspages_" + ns + "bstart" + dedupIdPrefix, "b"));
        if (withPages) {
            expectedTOCLinks.add(new TOCLink(1 + lastTitleLevel, getDriver().getCurrentUrl() + "#nspages_" + ns + "start" + dedupIdPrefix, "start"));
        }
        return expectedTOCLinks;
    }
}
