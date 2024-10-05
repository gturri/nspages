package nspages.printers;

import static org.junit.Assert.assertEquals;

import java.util.List;

import nspages.Helper;
import nspages.InternalLink;

import org.junit.Test;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.NoSuchElementException;

public class Test_tree extends Helper {
    /**
     * This test should display this tree:
     * +-+ section1
     * | +-- subsection1
     * | +-- subsection2
     * | +-+ subsection3
     * |   +-- subsubsection1
     * +-- section2
     * +-- section3
     */
    @Test
    public void testStandardCase(){
        generatePage("trees:start", "<nspages -tree -r -subns -nopages .:standard_tree>");
        assertGotStandardSubnsTree();
    }

    @Test
    public void treeIsCorrectlyRenderedEvenFromAPageNotAtTheRootLevel(){
        generatePage("trees:standard_tree:section1:subsection1:start", "<nspages -tree -r -subns -nopages ..:..>");
        assertGotStandardSubnsTree();
    }

    /**
     * Check we're correctly displaying the subnamespaces of trees:standard.
     * This checks
     * - The html structure
     * - The links
     * - The html level classes ("level1", "level2", ...)
     */
    private void assertGotStandardSubnsTree(){
        // Test 1st level nodes
        List<WebElement> firstLevelNodes = getFirstLevelChildren();
        assertEquals(3, firstLevelNodes.size());
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:start", "section1"), 1, firstLevelNodes.get(0), true);
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section2:start", "section2"), 1, firstLevelNodes.get(1), true);
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section3:start", "section3"), 1, firstLevelNodes.get(2), true);

        // Test 2nd level nodes
        //   Nodes below the 1st one
        List<WebElement> childrenOfFirstSection = getDirectChildren(firstLevelNodes.get(0));
        assertEquals(3, childrenOfFirstSection.size());
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:subsection1:start", "subsection1"), 2, childrenOfFirstSection.get(0), true);
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:subsection2:start", "subsection2"), 2, childrenOfFirstSection.get(1), true);
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:subsection3:start", "subsection3"), 2, childrenOfFirstSection.get(2), true);

        //  Other first level nodes should have no child
        assertEquals(0, getDirectChildren(firstLevelNodes.get(1)).size());
        assertEquals(0, getDirectChildren(firstLevelNodes.get(2)).size());

        // Test 3rd level node
        //   The first two 2nd-level nodes should have no child
        assertEquals(0, getDirectChildren(childrenOfFirstSection.get(0)).size());
        assertEquals(0, getDirectChildren(childrenOfFirstSection.get(1)).size());

        //   The third one should have one last child
        List<WebElement> thirdLevelNodes = getDirectChildren(childrenOfFirstSection.get(2));
        assertEquals(1, thirdLevelNodes.size());
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:subsection3:subsubsection1:start", "subsubsection1"), 3, thirdLevelNodes.get(0), true);

        // There should be not 4th level nodes
        assertEquals(0, getDirectChildren(thirdLevelNodes.get(0)).size());
    }

    private void assertSameLinksAndLevel(InternalLink expectedLink, int expectedLevel, WebElement actualNode, boolean isNode) {
        assertSameLinks(expectedLink, getSelfLink(actualNode));
        assertEquals("level" + expectedLevel + (isNode ? " node" : ""), actualNode.getAttribute("class"));
    }

    /**
     * This should display this tree:
     * +-+ section1
     *   +-+ section2
     *     +-+ section4
     *
     * This is a corner case on which we already had bug because the first node wasn't correctly computed
     */
    @Test
    public void rootIsCorrectlyComputedEvenInLinearTreeCase(){
        generatePage("trees:start", "<nspages -tree -r -subns -nopages .:linear_tree>");

        List<WebElement> firstLevelChildren = getFirstLevelChildren();
        assertEquals(1, firstLevelChildren.size());
        assertSameLinks(new InternalLink("trees:linear_tree:section1:start", "section1"), getSelfLink(firstLevelChildren.get(0)));

        // This test is only interested in testing the root has been correctly computed. No need for further assertions
    }

    /**
     * This test the special case which is fixed by version 2021-03-19 of nspages
     */
    @Test
    public void pagesAtTheRootOfTheWikiAreCorrectlyHandled(){
        // We only need one page for this test. More would make the test needlessly more complicated.
        // So we put a unique title to this page and filter on it with the -pregXXX option
        generatePage(":", "======Root======\n<nspages -tree -r -h1 -pregPagesTitleOn=\"/Root/\" >");
        List<WebElement> firstLevelChildren = getFirstLevelChildren();
        assertEquals(1, firstLevelChildren.size());
        assertSameLinks(new InternalLink("start", "Root"), getSelfLink(firstLevelChildren.get(0)));
    }

    @Test
    public void nsAtTheRootOfTheWikiAreCorrectlyHandled(){
        generatePage(":", "<nspages -subns -tree -r -pregNsOn=\"/ns_at_root/\" -pregPagesOn=\"/ns_at_root/\" -pregNsOff=\"/ns_at_root_with/\" -pregPagesOff=\"/ns_at_root_with/\"  -pagesInNs>");
        List<WebElement> firstLevelChildren = getFirstLevelChildren();
        assertEquals(1, firstLevelChildren.size()); // Ensure the -pregXX retrieve exactly what we want
        assertSameLinks(new InternalLink("ns_at_root:start", "ns_at_root"), getSelfLink(firstLevelChildren.get(0)));
    }

    /**
     * Building the tree will likely not preserve ordering.
     * This tests that we still correctly render a tree with every level sorted
     * (see issue #109)
     */
    @Test
    public void treeIsCorrectlySorted(){
        generatePage("trees:start", "<nspages -tree -r -subns -pagesInNs .:tree_tricky_to_sort -exclude>");

        List<WebElement> firstLevelNodes = getFirstLevelChildren();
        assertSameLinks(new InternalLink("trees:tree_tricky_to_sort:b:start", "b"), getSelfLink(firstLevelNodes.get(0)));
        assertSameLinks(new InternalLink("trees:tree_tricky_to_sort:d:start", "d"), getSelfLink(firstLevelNodes.get(1)));
        assertSameLinks(new InternalLink("trees:tree_tricky_to_sort:ns_with_no_main_page:start", "ns_with_no_main_page"), getSelfLink(firstLevelNodes.get(2)));
    }

    @Test
    public void testDisplayingPages(){
        // Exclude start pages to ensure empty subnamespaces aren't displayed
        generatePage("trees:start", "<nspages -tree -r .:standard_tree -exclude:start>");

        // Test the first level nodes
        List<WebElement> firstLevelChildren = getFirstLevelChildren();
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:page_at_root_level", "page_at_root_level"), 1, firstLevelChildren.get(0), false);
        assertEquals("trees:standard_tree:section1:", getNonLinkNodeInnerHTML(firstLevelChildren.get(1)));

        // Test second level nodes
        List<WebElement> section1Children = getDirectChildren(firstLevelChildren.get(1));
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:other_page_at_level2", "other_page_at_level2"), 2, section1Children.get(0), false);
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:page_at_level2", "page_at_level2"), 2, section1Children.get(1), false);
        assertEquals("trees:standard_tree:section1:subsection1:", getNonLinkNodeInnerHTML(section1Children.get(2)));

        // Test third level nodes
        List<WebElement> thirdLevelNodes = getDirectChildren(section1Children.get(2));
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:subsection1:other_page_at_level3", "other_page_at_level3"), 3, thirdLevelNodes.get(0), false);
        assertSameLinksAndLevel(new InternalLink("trees:standard_tree:section1:subsection1:page_at_level3", "page_at_level3"), 3, thirdLevelNodes.get(1), false);
    }

    @Test
    public void canBeCalledAtWikiRoot(){
        generatePage("start", "<nspages -tree -r>");
        // No need for assertions: the generatePage method already checks that there are no php warning
        // and no php error. This is all we need for this test
    }

    @Test
    public void noRegForBug120(){
        generatePage("trees:start", "<nspages -tree -r -subns -pagesInNs .:fix_120>");

        List<WebElement> firstLevelNodes = getFirstLevelChildren();

        // The important thing in bug #120 is that this is rendered as a link, we don't care about the other links
        // (this link should point at the page which represents this ns which is one level above)
        assertSameLinks(new InternalLink("trees:fix_120:ns", "ns"), getSelfLink(firstLevelNodes.get(0)));
    }

    @Test
    public void noRegForBug120AtWikiRoot(){
        generatePage(":", "<nspages -subns -tree -r -pregNsOn=\"/ns_at_root_with_main_page_above/\" -pregPagesOn=\"/ns_at_root_with_main_page_above/\" -pagesInNs>");
        List<WebElement> firstLevelChildren = getFirstLevelChildren();

        // We only need to test the 1st one (it's the only tricky one here)
        assertSameLinks(new InternalLink("ns_at_root_with_main_page_above", "ns_at_root_with_main_page_above"), getSelfLink(firstLevelChildren.get(0)));
    }

    private WebElement getSelfLink(WebElement node){
        try{
            return node.findElement(By.xpath("div/a"));
        }catch (NoSuchElementException e){
            // When DW renders a link to the current page it adds a <span class="curid">
            // between the <div class="li"> and the <a> so the previous xpath resolution
            // would fail in this case. Below is the correct one to use in this case.
            return node.findElement(By.xpath("div/span/a"));
        }
    }

    /**
     * When nspages is called without the -subns flag, then it displays namespace node as text only (no link).
     * This method retrieve the text of such nodes
     */
    private String getNonLinkNodeInnerHTML(WebElement node){
        return node.findElement(By.xpath("div")).getAttribute("innerHTML");
    }

    private List<WebElement> getFirstLevelChildren(){
        WebElement root = getDriver().findElement(By.cssSelector(".plugin_nspages"));
        return getDirectChildren(root);
    }

    private List<WebElement> getDirectChildren(WebElement node){
        return node.findElements(By.xpath("ul/li"));
    }
}
