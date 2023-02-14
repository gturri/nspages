package nspages;

import static org.junit.Assert.*;
import java.util.ArrayList;
import java.util.List;

import org.junit.Test;
import org.junit.matchers.JUnitMatchers;

public class Test_nsPath extends Helper {
	@Test
	public void defaultPath(){
		generatePage("autrens:start", "<nspages>");
		assertSameLinks(currentNsLinks());
	}

	@Test
	public void explicitDefaultPath(){
		generatePage("autrens:start", "<nspages .>");
		assertSameLinks(currentNsLinks());
	}

	@Test
	// This test does not really represent an actual use case since this
	// option is meant to be used in a sidebar (where the current ns is not
	// the same as the one of the page where the nspages plugin is used)
	// but this still makes sure that this case is not obviously broken
	public void sidebarOptionUsesCurrentNamespace(){
		generatePage("autrens:start", "<nspages -sidebar>");
		assertSameLinks(currentNsLinks());
	}

	@Test
	public void sidebarOptionDoesNotAcceptAnExplicitNs(){
		generatePage("autrens:start", "<nspages -sidebar some_name>");
		assertThat(getDriver().getPageSource(), JUnitMatchers.containsString("With the -sidebar option you cannot specify a namespace"));
	}

	@Test
	public void unsafePath(){
		generatePage("autrens:start", "<nspages ..:..>");
		assertThat(getDriver().getPageSource(), JUnitMatchers.containsString("this namespace doesn't exist:")); 
	}

	@Test
	public void relativePath(){
		generatePage("autrens:start", "<nspages ..:pregpages>");
		assertSameLinks(pregPagesNsLinks());

		generatePage("autrens:start", "<nspages .:..:pregpages>");
		assertSameLinks(pregPagesNsLinks());
	}

	@Test
	public void slashAsSeparator(){
		generatePage("autrens:start", "<nspages ../pregpages>");
		assertSameLinks(pregPagesNsLinks());
	}

	@Test
	public void semiColonAsSeparator(){
		generatePage("autrens:start", "<nspages ..;pregpages>");
		assertSameLinks(pregPagesNsLinks());
	}

	@Test
	public void tildeNamespace(){
		generatePage("pregpages", "<nspages ~>");
		assertSameLinks(pregPagesNsLinks());
	}

	@Test
	public void pathWithNoColonBeforeTheName(){
		// This format is supported by DW according to https://www.dokuwiki.org/namespaces so nspages users may want to use this
		generatePage("autrens:start", "<nspages ..pregpages>");
		assertSameLinks(pregPagesNsLinks());

		generatePage("autrens:start", "<nspages .:..pregpages>");
		assertSameLinks(pregPagesNsLinks());
	}

	@Test
	public void absolutePath(){
		generatePage("autrens:start", "<nspages :pregpages>");
		assertSameLinks(pregPagesNsLinks());
	}

	private List<InternalLink> currentNsLinks(){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("autrens:start", "start"));
		links.add(new InternalLink("autrens:subpage", "subpage"));
		return links;
	}

	private List<InternalLink> pregPagesNsLinks(){
		List<InternalLink> links = new ArrayList<InternalLink>();
		links.add(new InternalLink("pregpages:1p", "1p"));
		links.add(new InternalLink("pregpages:1p1", "1p1"));
		links.add(new InternalLink("pregpages:p1", "p1"));
		links.add(new InternalLink("pregpages:start", "start"));
		return links;
	}
}
