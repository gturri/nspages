package nspages;

import org.junit.Test;

import java.util.ArrayList;
import java.util.List;

public class Test_customTitle extends Helper {
    @Test
    public void withSimpleOption() {
        generatePage("customtitle:start", "======Some Title======\n\n"
        + "<nspages -customTitle=\"Custom title for page {title}\" >");

        List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
        expectedLinks.add(new InternalLink("customtitle:start", "Custom title for page Some Title"));
        assertSameLinks(expectedLinks);
    }

    @Test
    public void withMetaNotDefined() {
        // Here "user" is a correct metadata but since test are run with an anonymous user, this metadata isn't set.
        // This test ensure we cope correctly with this case
        generatePage("customtitle:start", "======Some Title======\n\n"
                + "<nspages -customTitle=\"{title} by {user}\" >");

        List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
        expectedLinks.add(new InternalLink("customtitle:start", "Some Title by "));
        assertSameLinks(expectedLinks);
    }

    @Test
    public void withForbiddenMetadata(){
        // "ip" is an actual metadata but it is not allowed by default (to prevent leaking private information).
        // This test ensures we don't replace those
        generatePage("customtitle:start", "======Some Title======\n\n"
                + "<nspages -customTitle=\"{title} by {ip}\" >");

        List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
        expectedLinks.add(new InternalLink("customtitle:start", "Some Title by ip"));
        assertSameLinks(expectedLinks);

    }

}
