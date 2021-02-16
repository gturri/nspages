package nspages;

import org.junit.Test;

import java.util.ArrayList;
import java.util.List;

/**
 * Those tests relies on the creation date of three pages. The test wiki is configured in a way that:
 * - old_page is the page created first
 * - new_page has been created in second
 * - recent_page has been created last
 */
public class Test_sortByMetadata extends Helper {
    @Test
    public void withoutOption(){
      generatePage("sort_by_metadata:start", "<nspages -exclude>");

      // Option isn't set => we should expect alphabetical order, as usual
      List<InternalLink> expectedLinks = new ArrayList<>();
      expectedLinks.add(buildInternalLink("new_page"));
      expectedLinks.add(buildInternalLink("old_page"));
      expectedLinks.add(buildInternalLink("recent_page"));

      assertSameLinks(expectedLinks);
    }

    @Test
    public void withOption(){
        generatePage("sort_by_metadata:start", "<nspages -exclude -sortByMetadata=\"date.created\">");

        List<InternalLink> expectedLinks = new ArrayList<>();
        expectedLinks.add(buildInternalLink("old_page"));
        expectedLinks.add(buildInternalLink("new_page"));
        expectedLinks.add(buildInternalLink("recent_page"));

        assertSameLinks(expectedLinks);
    }

    @Test
    public void withOptionAndReverse(){
        generatePage("sort_by_metadata:start", "<nspages -exclude -reverse -sortByMetadata=\"date.created\">");

        List<InternalLink> expectedLinks = new ArrayList<>();
        expectedLinks.add(buildInternalLink("recent_page"));
        expectedLinks.add(buildInternalLink("new_page"));
        expectedLinks.add(buildInternalLink("old_page"));

        assertSameLinks(expectedLinks);
    }

    private InternalLink buildInternalLink(String page){
        return new InternalLink("sort_by_metadata:" + page, page);
    }
}
