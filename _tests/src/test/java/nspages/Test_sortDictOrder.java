package nspages;

import org.junit.Test;

import java.util.ArrayList;
import java.util.List;

public class Test_sortDictOrder extends Helper {
    @Test
    public void withoutOption(){
        generatePage("start", "<nspages .:dictorder -h1>");

        List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
        expectedLinks.add(new InternalLink("dictorder:apfel", "apfel"));
        expectedLinks.add(new InternalLink("dictorder:unterfuhrung", "Unterführung"));

        assertSameLinks(expectedLinks);
    }

    @Test
    public void withOption(){
        generatePage("start", "<nspages .:dictorder -h1 -dictOrder=\"sk_SK\">");

        List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
        expectedLinks.add(new InternalLink("dictorder:unterfuhrung", "Unterführung"));
        expectedLinks.add(new InternalLink("dictorder:apfel", "apfel"));

        assertSameLinks(expectedLinks);
    }

    @Test
    public void withShortOption(){
        generatePage("start", "<nspages .:dictorder -h1 -dictionaryOrder=\"sk_SK\">");

        List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
        expectedLinks.add(new InternalLink("dictorder:unterfuhrung", "Unterführung"));
        expectedLinks.add(new InternalLink("dictorder:apfel", "apfel"));

        assertSameLinks(expectedLinks);
    }
}
